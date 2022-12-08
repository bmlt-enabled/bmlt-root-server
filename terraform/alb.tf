resource "aws_lb_target_group" "bmlt_latest" {
  name                 = "bmlt-latest"
  port                 = 8000
  protocol             = "HTTP"
  vpc_id               = data.aws_vpc.main.id
  deregistration_delay = 5

  health_check {
    path    = "/"
    matcher = "200"
  }
}

resource "aws_lb_target_group" "bmlt_unstable" {
  name                 = "bmlt-unstable"
  port                 = 8000
  protocol             = "HTTP"
  vpc_id               = data.aws_vpc.main.id
  deregistration_delay = 5

  health_check {
    path    = "/"
    matcher = "200"
  }
}

resource "aws_lb_listener_rule" "bmlt_unstable" {
  listener_arn = data.aws_lb_listener.main_443.arn

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.bmlt_unstable.arn
  }

  condition {
    host_header {
      values = [aws_route53_record.bmlt_unstable.name]
    }
  }
}

resource "aws_lb_listener_rule" "bmlt_latest" {
  listener_arn = data.aws_lb_listener.main_443.arn

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.bmlt_latest.arn
  }

  condition {
    host_header {
      values = [aws_route53_record.bmlt_latest.name]
    }
  }
}
