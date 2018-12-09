resource "aws_athena_database" "bmlt_root_alb_logs" {
  name   = "bmlt_root_alb_logs"
  bucket = "${aws_s3_bucket.bmlt_root_alb_logs_athena.bucket}"
}

resource "aws_s3_bucket" "bmlt_root_alb_logs_athena" {
  bucket        = "bmlt-root-alb-logs-athena"
  force_destroy = true
}

resource "aws_s3_bucket" "bmlt_root_alb_logs" {
  bucket        = "bmlt-root-alb-logs"
  force_destroy = true
}

resource "aws_s3_bucket_policy" "bmlt_root_alb_logs" {
  bucket = "${aws_s3_bucket.bmlt_root_alb_logs.id}"

  policy = <<EOF
{
  "Id": "Policy1521565569242",
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "Stmt1521565353380",
      "Action": "s3:PutObject",
      "Effect": "Allow",
      "Resource": "${aws_s3_bucket.bmlt_root_alb_logs.arn}/*",
      "Principal": {
        "AWS": "arn:aws:iam::127311923021:root"
      }
    }
  ]
}
EOF
}

resource "aws_security_group" "ecs_http_load_balancers" {
  vpc_id = "${aws_vpc.main.id}"
  name   = "bmlt-lb"

  ingress {
    protocol    = "tcp"
    from_port   = 80
    to_port     = 80
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port = 0
    to_port   = 0
    protocol  = "-1"

    cidr_blocks = ["0.0.0.0/0"]
  }
}

resource "aws_alb" "bmlt" {
  name            = "bmlt"
  subnets         = ["${aws_subnet.public_a.id}", "${aws_subnet.public_b.id}"]
  security_groups = ["${aws_security_group.ecs_http_load_balancers.id}"]

  access_logs {
    bucket  = "${aws_s3_bucket.bmlt_root_alb_logs.bucket}"
    enabled = true
  }

  tags {
    application = "bmlt"
    environment = "production"
  }
}

resource "aws_alb_target_group" "bmlt" {
  name     = "bmlt"
  port     = 80
  protocol = "HTTP"
  vpc_id   = "${aws_vpc.main.id}"

  deregistration_delay = 60

  health_check {
    path    = "/"
    matcher = "200"
  }
}

resource "aws_alb_listener" "bmlt_https" {
  load_balancer_arn = "${aws_alb.bmlt.id}"
  port              = 80
  protocol          = "HTTP"

  //certificate_arn   = "${var.certificate_arn}"

  default_action {
    target_group_arn = "${aws_alb_target_group.bmlt.id}"
    type             = "forward"
  }
}
