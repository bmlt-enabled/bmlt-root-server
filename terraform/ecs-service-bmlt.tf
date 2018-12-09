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
  name = "${aws_iam_role.bmlt_lb.name}"
  role = "${aws_iam_role.bmlt_lb.name}"

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
        "elasticloadbalancing:RegisterTargets"
      ],
      "Resource": "*"
    }
  ]
}
EOF
}

resource "aws_db_subnet_group" "bmlt" {
  name       = "bmlt"
  subnet_ids = ["${aws_subnet.public_a.id}", "${aws_subnet.public_b.id}"]
}

resource "aws_ecs_task_definition" "bmlt" {
  family = "bmlt"

  container_definitions = <<EOF
[
  {
    "name": "bmlt-root-server",
    "volumesFrom": [],
    "extraHosts": null,
    "dnsServers": null,
    "disableNetworking": null,
    "dnsSearchDomains": null,
    "portMappings": [
      {
        "hostPort": 0,
        "containerPort": 80,
        "protocol": "tcp"
      }
    ],
    "hostname": null,
    "essential": true,
    "entryPoint": null,
    "mountPoints": [],
    "ulimits": null,
    "dockerSecurityOptions": null,
    "environment": [
      {
        "name": "GKEY",
        "value": "AIzaSyD4BPAvDHL4CiRcFORdoUCpqwVuVz1F9r8"
      },
      {
        "name": "DBNAME",
        "value": "bmlt"
      },
      {
        "name": "DBUSER",
        "value": "bmlt_user"
      },
      {
        "name": "DBPASSWORD",
        "value": "bmlt_password"
      },
      {
        "name": "DBSERVER",
        "value": "bmlt-db"
      },
      {
        "name": "DBPREFIX",
        "value": "na"
      },
      {
        "name": "FORCE_HTTPS_URLS",
        "value": "true"
      },
      {
        "name": "DO_NOT_FORCE_PORT",
        "value": "true"
      }
    ],
    "links": ["bmlt-db"],
    "workingDirectory": "/tmp",
    "readonlyRootFilesystem": null,
    "image": "bmltenabled/bmlt-root-server:latest",
    "command": [
      "/bin/bash",
      "/tmp/start-bmlt.sh"
    ],
    "user": null,
    "dockerLabels": null,
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.bmlt_root.name}",
        "awslogs-region": "us-east-1",
        "awslogs-stream-prefix": "bmlt-root"
      }
    },
    "memoryReservation": 128,
    "privileged": null,
    "linuxParameters": {
      "initProcessEnabled": true
    }
  },
  {
    "name": "bmlt-db",
    "volumesFrom": [],
    "extraHosts": null,
    "dnsServers": null,
    "disableNetworking": null,
    "dnsSearchDomains": null,
    "portMappings": [
      {
        "containerPort": 3306,
        "protocol": "tcp"
      }
    ],
    "hostname": null,
    "essential": true,
    "entryPoint": ["docker-entrypoint.sh"],
    "mountPoints": [],
    "ulimits": null,
    "dockerSecurityOptions": null,
    "environment": [
      {
        "name": "MYSQL_ROOT_PASSWORD",
        "value": "bmlt_root_password"
      },
      {
        "name": "MYSQL_DATABASE",
        "value": "bmlt"
      },
      {
        "name": "MYSQL_USER",
        "value": "bmlt_user"
      },
      {
        "name": "MYSQL_PASSWORD",
        "value": "bmlt_password"
      }
    ],
    "links": [],
    "workingDirectory": "/tmp",
    "readonlyRootFilesystem": null,
    "image": "bmltenabled/bmlt-root-server-sample-db:latest",
    "command": ["mysqld"],
    "user": null,
    "dockerLabels": null,
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.bmlt_db.name}",
        "awslogs-region": "us-east-1",
        "awslogs-stream-prefix": "bmlt-db"
      }
    },
    "memoryReservation": 128,
    "privileged": null,
    "linuxParameters": {
      "initProcessEnabled": true
    }
  }
]
EOF
}

resource "aws_ecs_service" "bmlt" {
  name            = "bmlt"
  cluster         = "${aws_ecs_cluster.main.id}"
  desired_count   = 1
  iam_role        = "${aws_iam_role.bmlt_lb.name}"
  task_definition = "${aws_ecs_task_definition.bmlt.arn}"

  deployment_minimum_healthy_percent = 100

  load_balancer {
    target_group_arn = "${aws_alb_target_group.bmlt.id}"
    container_name   = "bmlt-root-server"
    container_port   = 80
  }

  depends_on = [
    "aws_iam_role_policy.bmlt_lb",
    "aws_alb_listener.bmlt_https",
  ]
}
