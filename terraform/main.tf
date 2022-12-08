terraform {
  required_version = ">= 1.3.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 4.40"
    }
    template = {
      source  = "hashicorp/template"
      version = "~> 2.2.0"
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
