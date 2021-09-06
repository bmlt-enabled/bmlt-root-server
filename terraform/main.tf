terraform {
  backend "s3" {
    bucket         = "mvana-account-terraform"
    key            = "state/bmlt-root-server"
    region         = "us-east-1"
    dynamodb_table = "mvana-account-terraform"
    profile        = "mvana"
  }
}

provider "aws" {
  region  = "us-east-1"
  profile = "mvana"
}

provider "aws" {
  alias   = "bmlt"
  profile = "bmlt"
  region  = "us-east-1"
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

data "aws_subnet_ids" "main" {
  vpc_id = data.aws_vpc.main.id
}
