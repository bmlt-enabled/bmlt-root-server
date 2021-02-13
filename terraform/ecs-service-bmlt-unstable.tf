resource "aws_ecs_task_definition" "bmlt_unstable" {
  family = "bmlt-unstable"

  container_definitions = <<EOF
[
  {
    "name": "bmlt-root-server",
    "repositoryCredentials": {
        "credentialsParameter": "${aws_secretsmanager_secret.docker_repository.arn}"
    },
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
        "value": "${var.GOOGLE_API_KEY}"
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
      }
    ],
    "links": ["bmlt-db"],
    "workingDirectory": "/tmp",
    "readonlyRootFilesystem": null,
    "image": "bmltenabled/bmlt-root-server:unstable",
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
    "repositoryCredentials": {
        "credentialsParameter": "${aws_secretsmanager_secret.docker_repository.arn}"
    },
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
    "image": "bmltenabled/bmlt-root-server-sample-db:unstable",
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

resource "aws_ecs_service" "bmlt_unstable" {
  name            = "bmlt-unstable"
  cluster         = aws_ecs_cluster.main.id
  desired_count   = 1
  iam_role        = aws_iam_role.bmlt_lb.name
  task_definition = aws_ecs_task_definition.bmlt_unstable.arn

  deployment_minimum_healthy_percent = 100

  load_balancer {
    target_group_arn = aws_alb_target_group.bmlt_unstable.id
    container_name   = "bmlt-root-server"
    container_port   = 80
  }

  depends_on = [
    aws_iam_role_policy.bmlt_lb,
    aws_alb_listener.bmlt_https,
  ]
}

