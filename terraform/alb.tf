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

resource "aws_alb_listener_rule" "bmlt_unstable" {
  listener_arn = data.aws_lb_listener.main_443.arn

  action {
    type             = "forward"
    target_group_arn = aws_alb_target_group.bmlt_unstable.arn
  }

  condition {
    host_header {
      values = [aws_route53_record.bmlt_unstable.name]
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
      values = [aws_route53_record.bmlt_latest.name]
    }
  }
}
