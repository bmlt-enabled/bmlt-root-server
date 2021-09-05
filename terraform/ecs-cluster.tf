resource "aws_ecs_cluster" "main" {
  name = "bmlt"
}

resource "aws_iam_role" "cluster_instance" {
  name = local.cluster_name

  assume_role_policy = jsonencode(
    {
      Version = "2012-10-17",
      Statement = [
        {
          Sid    = "",
          Effect = "Allow",
          Principal = {
            Service = "ec2.amazonaws.com"
          },
          Action = "sts:AssumeRole"
        }
      ]
    }
  )

}

resource "aws_iam_role_policy_attachment" "attach_ecs_policy" {
  role       = aws_iam_role.cluster_instance.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonEC2ContainerServiceforEC2Role"
}

resource "aws_iam_role_policy_attachment" "attach_ecr_policy" {
  role       = aws_iam_role.cluster_instance.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryReadOnly"
}

resource "aws_iam_role_policy_attachment" "attach_ssm_policy" {
  role       = aws_iam_role.cluster_instance.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

resource "aws_iam_role_policy" "allow_logging_policy" {
  name = aws_iam_role.cluster_instance.name
  role = aws_iam_role.cluster_instance.name

  policy = jsonencode(
    {
      Version = "2012-10-17",
      Statement = [
        {
          Effect = "Allow",
          Action = [
            "logs:CreateLogGroup",
            "logs:CreateLogStream",
            "logs:PutLogEvents",
            "logs:DescribeLogStreams"
          ],
          Resource = [
            "arn:aws:logs:*:*:*"
          ]
        }
      ]
    }
  )
}

resource "aws_iam_instance_profile" "cluster" {
  name = local.cluster_name
  role = aws_iam_role.cluster_instance.name
}

resource "aws_autoscaling_group" "cluster" {
  name                 = local.cluster_name
  vpc_zone_identifier  = data.aws_subnet_ids.main.ids
  min_size             = 1
  max_size             = 1
  desired_capacity     = 1
  launch_configuration = aws_launch_configuration.cluster.name

  tags = [
    {
      key                 = "Name"
      value               = "bmlt-ecs"
      propagate_at_launch = true
    },
    {
      key                 = "application"
      value               = "bmlt"
      propagate_at_launch = true
    },
    {
      key                 = "environment"
      value               = "production"
      propagate_at_launch = true
    },
  ]
}

resource "aws_security_group" "cluster" {
  description = "controls direct access to cluster container instances"
  vpc_id      = data.aws_vpc.main.id
  name        = local.cluster_name

  ingress {
    protocol  = "tcp"
    from_port = 32768
    to_port   = 61000

    security_groups = [
      aws_security_group.ecs_http_load_balancers.id,
      "sg-0573eb4a1df88751a", # the tomato alb
    ]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

locals {
  cluster_name = aws_ecs_cluster.main.name
}

data "aws_ami" "ecs" {
  most_recent = true

  owners = ["amazon"]

  filter {
    name   = "name"
    values = ["amzn2-ami-ecs-hvm-*-x86_64-ebs"]
  }
}

resource "aws_launch_configuration" "cluster" {
  security_groups             = [aws_security_group.cluster.id]
  key_name                    = aws_key_pair.main.key_name
  image_id                    = data.aws_ami.ecs.image_id
  instance_type               = "t3a.micro"
  iam_instance_profile        = aws_iam_instance_profile.cluster.name
  associate_public_ip_address = false

  user_data = data.template_cloudinit_config.cluster.rendered

  lifecycle {
    create_before_destroy = true
  }
}

data "template_cloudinit_config" "cluster" {
  part {
    content_type = "text/x-shellscript"
    content      = <<EOF
#!/usr/bin/env bash

echo ECS_CLUSTER=${local.cluster_name} >> /etc/ecs/ecs.config

# Install awslogs and the jq
yum install -y awslogs jq

# Inject the CloudWatch Logs configuration file contents
cat > /etc/awslogs/awslogs.conf <<- EOF
[general]
state_file = /var/lib/awslogs/agent-state

[/var/log/dmesg]
file = /var/log/dmesg
log_group_name = ${local.cluster_name}/var/log/dmesg
log_stream_name = ${local.cluster_name}/{container_instance_id}

[/var/log/messages]
file = /var/log/messages
log_group_name = ${local.cluster_name}/var/log/messages
log_stream_name = ${local.cluster_name}/{container_instance_id}
datetime_format = %b %d %H:%M:%S

[/var/log/docker]
file = /var/log/docker
log_group_name = ${local.cluster_name}/var/log/docker
log_stream_name = ${local.cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%S.%f

[/var/log/ecs/ecs-init.log]
file = /var/log/ecs/ecs-init.log.*
log_group_name = ${local.cluster_name}/var/log/ecs/ecs-init.log
log_stream_name = ${local.cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%SZ

[/var/log/ecs/ecs-agent.log]
file = /var/log/ecs/ecs-agent.log.*
log_group_name = ${local.cluster_name}/var/log/ecs/ecs-agent.log
log_stream_name = ${local.cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%SZ

[/var/log/ecs/audit.log]
file = /var/log/ecs/audit.log.*
log_group_name = ${local.cluster_name}/var/log/ecs/audit.log
log_stream_name = ${local.cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%SZ
EOF
  }

  part {
    content_type = "text/x-shellscript"
    content      = <<BSEOF
#!/usr/bin/env bash
# Write the awslogs bootstrap script to /usr/local/bin/bootstrap-awslogs.sh
cat > /usr/local/bin/bootstrap-awslogs.sh <<- 'EOF'
#!/usr/bin/env bash
exec 2>>/var/log/ecs/cloudwatch-logs-start.log
set -x
until curl -s http://localhost:51678/v1/metadata
do
	sleep 1
done

# Set the region to send CloudWatch Logs data to (the region where the container instance is located)
cp /etc/awslogs/awscli.conf /etc/awslogs/awscli.conf.bak
region=$(curl -s 169.254.169.254/latest/dynamic/instance-identity/document | jq -r .region)
sed -i -e "s/region = .*/region = $region/g" /etc/awslogs/awscli.conf

# Grab the cluster and container ip address of the node
cluster=$(curl -s http://localhost:51678/v1/metadata | jq -r '. | .Cluster')
container_instance_id=$(curl 169.254.169.254/latest/meta-data/local-ipv4)

# Replace the cluster name and container instance ID placeholders with the actual values
cp /etc/awslogs/awslogs.conf /etc/awslogs/awslogs.conf.bak
sed -i -e "s/{cluster}/$cluster/g" /etc/awslogs/awslogs.conf
sed -i -e "s/{container_instance_id}/$container_instance_id/g" /etc/awslogs/awslogs.conf
BSEOF
  }

  part {
    content_type = "text/x-shellscript"
    content      = <<LSEOF
#!/usr/bin/env bash
# Write the bootstrap-awslogs systemd unit file to /etc/systemd/system/bootstrap-awslogs.service
cat > /etc/systemd/system/bootstrap-awslogs.service <<- EOF
[Unit]
Description=Bootstrap awslogs agent
Requires=ecs.service
After=ecs.service
Before=awslogsd.service
[Service]
Type=oneshot
RemainAfterExit=yes
ExecStart=/usr/local/bin/bootstrap-awslogs.sh
[Install]
WantedBy=awslogsd.service
EOF
LSEOF
  }

  part {
    content_type = "text/x-shellscript"
    content      = <<SSEOF
#!/usr/bin/env bash

# Start logs
chmod +x /usr/local/bin/bootstrap-awslogs.sh
systemctl daemon-reload
systemctl enable bootstrap-awslogs.service
systemctl enable awslogsd.service
systemctl start awslogsd.service --no-block
SSEOF
  }
}

# IAM Role for ECS Service interaction with load balancer
resource "aws_iam_role" "bmlt_lb" {
  name = "bmlt-lb"

  assume_role_policy = jsonencode(
    {
      Version = "2008-10-17",
      Statement = [
        {
          Sid    = "",
          Effect = "Allow",
          Principal = {
            Service = "ecs.amazonaws.com"
          },
          Action = "sts:AssumeRole"
        }
      ]
    }
  )
}

resource "aws_iam_role_policy" "bmlt_lb" {
  name = aws_iam_role.bmlt_lb.name
  role = aws_iam_role.bmlt_lb.name

  policy = jsonencode(
    {
      Version = "2012-10-17",
      Statement = [
        {
          Effect = "Allow",
          Action = [
            "ec2:Describe*",
            "elasticloadbalancing:DeregisterInstancesFromLoadBalancer",
            "elasticloadbalancing:DeregisterTargets",
            "elasticloadbalancing:Describe*",
            "elasticloadbalancing:RegisterInstancesWithLoadBalancer",
            "elasticloadbalancing:RegisterTargets"
          ],
          Resource = "*"
        }
      ]
    }
  )
}

data "aws_iam_policy_document" "ecs_task_role_assume_policy" {
  statement {
    sid    = "ecsTask"
    effect = "Allow"
    principals {
      type        = "Service"
      identifiers = ["ecs-tasks.amazonaws.com"]
    }
    actions = ["sts:AssumeRole"]
  }
}

data "aws_iam_policy_document" "ecs_execute_command" {
  statement {
    effect = "Allow"
    actions = [
      "ssmmessages:CreateControlChannel",
      "ssmmessages:CreateDataChannel",
      "ssmmessages:OpenControlChannel",
      "ssmmessages:OpenDataChannel"
    ]
    resources = ["*"]
  }
}

resource "aws_iam_policy" "ecs_execute_command" {
  name        = "ecs-execute-command"
  description = "Allows execution of remote commands on ECS"
  policy      = data.aws_iam_policy_document.ecs_execute_command.json
}

resource "aws_iam_role" "ecs_task_role" {
  name               = "ecs-exec-task-role"
  assume_role_policy = data.aws_iam_policy_document.ecs_task_role_assume_policy.json

  tags = { Name = "ecs-exec-task-role" }
}

resource "aws_iam_role_policy_attachment" "ecs_task_role_execute_command_attachment" {
  policy_arn = aws_iam_policy.ecs_execute_command.arn
  role       = aws_iam_role.ecs_task_role.name
}
