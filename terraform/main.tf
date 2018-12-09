//terraform {
//  backend "s3" {
//    bucket  = "bmlt-tfstate"
//    key     = "state"
//    region  = "us-east-1"
//    profile = "personal"
//  }
//}

provider "aws" {
  region = "us-east-1"
}
