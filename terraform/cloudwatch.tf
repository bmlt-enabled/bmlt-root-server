resource "aws_cloudwatch_log_group" "bmlt_root" {
  name              = "bmlt-root"
  retention_in_days = 7
}

resource "aws_cloudwatch_log_group" "bmlt_db" {
  name              = "bmlt-db"
  retention_in_days = 7
}

resource "aws_cloudwatch_log_group" "aggregator" {
  name              = "aggregator"
  retention_in_days = 7
}

resource "aws_cloudwatch_log_group" "aggregator_import" {
  name              = "aggregator-import"
  retention_in_days = 7
}

resource "aws_cloudwatch_log_group" "aggregator_init" {
  name              = "aggregator-init"
  retention_in_days = 7
}

resource "aws_cloudwatch_event_rule" "aggregator_import" {
  name                = "aggregator-import"
  description         = "Kicks off aggregator import every 4 hours"
  schedule_expression = "rate(4 hours)"
}

resource "aws_cloudwatch_event_target" "aggregator_import" {
  target_id = "aggregator-import"
  arn       = aws_ecs_cluster.aggregator.arn
  rule      = aws_cloudwatch_event_rule.aggregator_import.name
  role_arn  = data.aws_iam_role.ecs_events.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.aggregator_import.arn
    launch_type         = "EC2"
  }
}
