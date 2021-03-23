resource "aws_cloudwatch_log_group" "bmlt_root" {
  name              = "bmlt-root"
  retention_in_days = 7
}

resource "aws_cloudwatch_log_group" "bmlt_db" {
  name              = "bmlt-db"
  retention_in_days = 7
}
