terraform {
  backend "s3" {
    bucket         = "mvana-account-terraform"
    key            = "state/bmlt-root-server"
    region         = "us-east-1"
    dynamodb_table = "mvana-account-terraform"
    #    profile        = "mvana"
  }
}

provider "aws" {
  region = "us-east-1"
  #  profile = "mvana"
}

data "aws_vpc" "main" {
  filter {
    name   = "tag:Name"
    values = ["tomato"]
  }
}

data "aws_lb_listener" "main_443" {
  load_balancer_arn = data.aws_lb.main.arn
  port              = 443
}

data "aws_lb" "main" {
  name = "tomato"
}

data "aws_subnets" "main" {
  filter {
    name   = "vpc-id"
    values = [data.aws_vpc.main.id]
  }
}

data "aws_secretsmanager_secret" "docker" {
  name = "docker"
}
