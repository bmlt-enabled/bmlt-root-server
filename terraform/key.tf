resource "aws_key_pair" "main" {
  key_name   = "bmlt"
  public_key = file(pathexpand("id_rsa.pub"))
}
