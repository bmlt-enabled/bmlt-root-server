resource "aws_ecrpublic_repository" "bmlt-root-server" {
  repository_name = "bmlt-root-server"

  catalog_data {
    description       = "BMLT Root Server"
    architectures     = ["x86-64"]
    operating_systems = ["Linux"]
  }
}

resource "aws_ecrpublic_repository" "bmlt-root-server-base" {
  repository_name = "bmlt-root-server-base"

  catalog_data {
    description       = "BMLT Root Server BASE"
    architectures     = ["x86-64"]
    operating_systems = ["Linux"]
  }
}

resource "aws_ecrpublic_repository" "bmlt-root-server-sample-db" {
  repository_name = "bmlt-root-server-sample-db"

  catalog_data {
    description       = "BMLT Root Server Sample DB"
    architectures     = ["x86-64"]
    operating_systems = ["Linux"]
  }
}
