resource "aws_ecs_cluster" "main" {
  name = "bmlt"
}

resource "aws_iam_role" "cluster_instance" {
  name = aws_ecs_cluster.main.name

  assume_role_policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "",
      "Effect": "Allow",
      "Principal": {
        "Service": "ec2.amazonaws.com"
      },
      "Action": "sts:AssumeRole"
    }
  ]
}
EOF

}

resource "aws_iam_role_policy_attachment" "attach_ecs_policy" {
  role       = aws_iam_role.cluster_instance.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonEC2ContainerServiceforEC2Role"
}

resource "aws_iam_role_policy_attachment" "attach_ecr_policy" {
  role       = aws_iam_role.cluster_instance.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryReadOnly"
}

resource "aws_iam_role_policy" "allow_logging_policy" {
  name = aws_iam_role.cluster_instance.name
  role = aws_iam_role.cluster_instance.name

  policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
          "logs:CreateLogGroup",
          "logs:CreateLogStream",
          "logs:PutLogEvents",
          "logs:DescribeLogStreams"
      ],
      "Resource": [
        "arn:aws:logs:*:*:*"
      ]
    }
  ]
}
EOF

}

resource "aws_iam_instance_profile" "cluster" {
  name = aws_ecs_cluster.main.name
  role = aws_iam_role.cluster_instance.name
}

resource "aws_autoscaling_group" "cluster" {
  name                 = aws_ecs_cluster.main.name
  vpc_zone_identifier  = [aws_subnet.public_a.id, aws_subnet.public_b.id]
  min_size             = 1
  max_size             = 1
  desired_capacity     = 1
  launch_configuration = aws_launch_configuration.cluster.name
}

resource "aws_security_group" "cluster" {
  description = "controls direct access to cluster container instances"
  vpc_id      = aws_vpc.main.id
  name        = aws_ecs_cluster.main.name

  ingress {
    protocol  = "tcp"
    from_port = 32768
    to_port   = 61000

    security_groups = [
      aws_security_group.ecs_http_load_balancers.id,
    ]
  }

  //ingress {
  //  protocol    = "tcp"
  //  from_port   = 22
  //  to_port     = 22
  //  cidr_blocks = ["0.0.0.0/0"]
  //}

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

locals {
  user_data = templatefile("${path.module}/templates/user_data.sh",
    {
      ecs_config        = "echo '' > /etc/ecs/ecs.config"
      ecs_logging       = "[\"json-file\",\"awslogs\"]"
      cluster_name      = aws_ecs_cluster.main.name
      cloudwatch_prefix = "bmlt"
  })
}

data "aws_ami" "ecs" {
  most_recent = true

  owners = ["amazon"]

  filter {
    name   = "name"
    values = ["amzn-ami-*-amazon-ecs-optimized"]
  }
}

resource "aws_launch_configuration" "cluster" {
  security_groups             = [aws_security_group.cluster.id]
  key_name                    = aws_key_pair.main.key_name
  image_id                    = data.aws_ami.ecs.image_id
  instance_type               = "t3a.micro"
  iam_instance_profile        = aws_iam_instance_profile.cluster.name
  associate_public_ip_address = false

  user_data = local.user_data

  lifecycle {
    create_before_destroy = true
  }
}

# IAM Role for ECS Service interaction with load balancer
resource "aws_iam_role" "bmlt_lb" {
  name = "bmlt-lb"

  assume_role_policy = <<EOF
{
  "Version": "2008-10-17",
  "Statement": [
    {
      "Sid": "",
      "Effect": "Allow",
      "Principal": {
        "Service": "ecs.amazonaws.com"
      },
      "Action": "sts:AssumeRole"
    }
  ]
}
EOF

}

resource "aws_iam_role_policy" "bmlt_lb" {
  name = aws_iam_role.bmlt_lb.name
  role = aws_iam_role.bmlt_lb.name

  policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "ec2:Describe*",
        "elasticloadbalancing:DeregisterInstancesFromLoadBalancer",
        "elasticloadbalancing:DeregisterTargets",
        "elasticloadbalancing:Describe*",
        "elasticloadbalancing:RegisterInstancesWithLoadBalancer",
        "elasticloadbalancing:RegisterTargets",
        "secretsmanager:GetSecretValue",
        "kms:Decrypt"
      ],
      "Resource": "*"
    }
  ]
}
EOF

}

