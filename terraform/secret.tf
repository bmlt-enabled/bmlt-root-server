resource "aws_kms_key" "docker_repository" {
  description         = "used to encrypt and decrypt docker repository credentials"
  enable_key_rotation = true

  tags = {
    Name = "bmlt-docker"
  }
}

resource "aws_secretsmanager_secret" "docker_repository" {
  name_prefix = "bmlt-docker"
  kms_key_id  = aws_kms_key.docker_repository.id

  tags = {
    Name = "bmlt-docker"
  }
}

resource "aws_secretsmanager_secret_version" "docker_repository" {
  secret_id     = aws_secretsmanager_secret.docker_repository.id
  secret_string = "{ \"username\": \"${var.DOCKER_USERNAME}\", \"password\": \"${var.DOCKER_PASSWORD}\" }"
}
