resource "aws_athena_database" "bmlt_root_alb_logs" {
  name   = "bmlt_root_alb_logs"
  bucket = aws_s3_bucket.bmlt_root_alb_logs_athena.bucket
}

resource "aws_s3_bucket" "bmlt_root_alb_logs_athena" {
  bucket_prefix = "bmlt-root-alb-logs-athena"
  force_destroy = true
}

resource "aws_s3_bucket" "bmlt_root_alb_logs" {
  bucket_prefix = "bmlt-root-alb-logs"
  force_destroy = true
}

resource "aws_s3_bucket_policy" "bmlt_root_alb_logs" {
  bucket = aws_s3_bucket.bmlt_root_alb_logs.id

  policy = jsonencode(
    {
      Id      = "Policy1521565569242",
      Version = "2012-10-17",
      Statement = [
        {
          Sid      = "Stmt1521565353380",
          Action   = "s3:PutObject",
          Effect   = "Allow",
          Resource = "${aws_s3_bucket.bmlt_root_alb_logs.arn}/*",
          Principal = {
            AWS = "arn:aws:iam::127311923021:root"
          }
        }
      ]
    }
  )
}

resource "aws_security_group" "ecs_http_load_balancers" {
  vpc_id = data.aws_vpc.main.id
  name   = "bmlt-lb"

  ingress {
    protocol    = "tcp"
    from_port   = 443
    to_port     = 443
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port = 0
    to_port   = 0
    protocol  = "-1"

    cidr_blocks = ["0.0.0.0/0"]
  }
}

resource "aws_alb_target_group" "bmlt_latest" {
  name     = "bmlt-latest"
  port     = 80
  protocol = "HTTP"
  vpc_id   = data.aws_vpc.main.id

  deregistration_delay = 60

  health_check {
    path    = "/"
    matcher = "200"
  }
}

resource "aws_alb_target_group" "bmlt_unstable" {
  name     = "bmlt-unstable"
  port     = 80
  protocol = "HTTP"
  vpc_id   = data.aws_vpc.main.id

  deregistration_delay = 60

  health_check {
    path    = "/"
    matcher = "200"
  }
}

data "aws_lb_listener" "main_443" {
  load_balancer_arn = data.aws_lb.main.arn
  port              = 443
}

data "aws_lb" "main" {
  name = "tomato"
}

resource "aws_alb_listener_rule" "bmlt_unstable" {
  listener_arn = data.aws_lb_listener.main_443.arn

  action {
    type             = "forward"
    target_group_arn = aws_alb_target_group.bmlt_unstable.arn
  }

  condition {
    host_header {
      values = ["unstable.aws.bmlt.app"]
    }
  }
}

resource "aws_alb_listener_rule" "bmlt_latest" {
  listener_arn = data.aws_lb_listener.main_443.arn

  action {
    type             = "forward"
    target_group_arn = aws_alb_target_group.bmlt_latest.arn
  }

  condition {
    host_header {
      values = ["latest.aws.bmlt.app"]
    }
  }
}
