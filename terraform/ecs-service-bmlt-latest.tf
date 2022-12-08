resource "aws_ecs_task_definition" "bmlt_latest" {
  family             = "bmlt-latest"
  task_role_arn      = data.aws_iam_role.ecs_task.arn
  execution_role_arn = data.aws_iam_role.ecs_task.arn

  container_definitions = jsonencode(
    [
      {
        name              = "bmlt-root-server",
        volumesFrom       = [],
        extraHosts        = null,
        dnsServers        = null,
        disableNetworking = null,
        dnsSearchDomains  = null,
        portMappings = [
          {
            hostPort      = 0,
            containerPort = 8000,
            protocol      = "tcp"
          }
        ],
        hostname              = null,
        essential             = true,
        entryPoint            = null,
        mountPoints           = [],
        ulimits               = null,
        dockerSecurityOptions = null,
        environment = [
          {
            name  = "GKEY",
            value = var.GOOGLE_API_KEY
          },
          {
            name  = "DB_DATABASE",
            value = "rootserver"
          },
          {
            name  = "DB_USER",
            value = "rootserver"
          },
          {
            name  = "DB_PASSWORD",
            value = "rootserver"
          },
          {
            name  = "DB_HOST",
            value = "bmlt-db"
          },
          {
            name  = "DB_PREFIX",
            value = "na"
          }
        ],
        links                  = ["bmlt-db"],
        workingDirectory       = "/tmp",
        readonlyRootFilesystem = null,
        image                  = "bmltenabled/bmlt-root-server:latest",
        repositoryCredentials = {
          credentialsParameter = data.aws_secretsmanager_secret.docker.arn
        },
        command = [
          "/bin/bash",
          "/tmp/start-bmlt.sh"
        ],
        user         = null,
        dockerLabels = null,
        logConfiguration = {
          logDriver = "awslogs",
          options = {
            awslogs-group         = aws_cloudwatch_log_group.bmlt_root.name,
            awslogs-region        = "us-east-1",
            awslogs-stream-prefix = "bmlt-root"
          }
        },
        memoryReservation = 256,
        privileged        = null,
        linuxParameters = {
          initProcessEnabled = true
        }
      },
      {
        name              = "bmlt-db",
        volumesFrom       = [],
        extraHosts        = null,
        dnsServers        = null,
        disableNetworking = null,
        dnsSearchDomains  = null,
        portMappings = [
          {
            containerPort = 3306,
            protocol      = "tcp"
          }
        ],
        hostname              = null,
        essential             = true,
        mountPoints           = [],
        ulimits               = null,
        dockerSecurityOptions = null,
        environment = [
          {
            name  = "MARIADB_ROOT_PASSWORD",
            value = "rootserver"
          },
          {
            name  = "MARIADB_DATABASE",
            value = "rootserver"
          },
          {
            name  = "MARIADB_USER",
            value = "rootserver"
          },
          {
            name  = "MARIADB_PASSWORD",
            value = "rootserver"
          }
        ],
        links                  = [],
        workingDirectory       = "/tmp",
        readonlyRootFilesystem = null,
        image                  = "bmltenabled/bmlt-root-server-sample-db:latest",
        repositoryCredentials = {
          credentialsParameter = data.aws_secretsmanager_secret.docker.arn
        },
        user         = null,
        dockerLabels = null,
        logConfiguration = {
          logDriver = "awslogs",
          options = {
            awslogs-group         = aws_cloudwatch_log_group.bmlt_db.name,
            awslogs-region        = "us-east-1",
            awslogs-stream-prefix = "bmlt-db"
          }
        },
        memoryReservation = 144,
        privileged        = null,
        linuxParameters = {
          initProcessEnabled = true
        }
      }
    ]
  )
}

resource "aws_ecs_service" "bmlt_latest" {
  name                               = "bmlt-latest"
  cluster                            = aws_ecs_cluster.main.id
  desired_count                      = 1
  iam_role                           = data.aws_iam_role.ecs_service.name
  task_definition                    = aws_ecs_task_definition.bmlt_latest.arn
  enable_execute_command             = true
  deployment_minimum_healthy_percent = 100

  load_balancer {
    target_group_arn = aws_lb_target_group.bmlt_latest.id
    container_name   = "bmlt-root-server"
    container_port   = 8000
  }

  lifecycle {
    ignore_changes = [task_definition]
  }
}
