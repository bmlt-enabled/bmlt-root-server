terraform {
  required_version = ">= 1.3.0, <= 2.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
    cloudinit = {
      source  = "hashicorp/cloudinit"
      version = "~> 2.3"
    }
  }

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
