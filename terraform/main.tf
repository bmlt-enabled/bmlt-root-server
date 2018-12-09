terraform {
  backend "s3" {
    bucket  = "bmlt-terraform-remote-state"
    key     = "state"
    region  = "us-east-1"
  }
}

provider "aws" {
  region  = "us-east-1"
}
