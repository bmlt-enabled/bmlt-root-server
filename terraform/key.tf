data "template_file" "pubkey" {
  template = file(pathexpand("id_rsa.pub"))
}

resource "aws_key_pair" "main" {
  key_name   = "bmlt"
  public_key = data.template_file.pubkey.rendered
}

