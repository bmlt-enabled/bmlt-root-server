resource "aws_acm_certificate" "mvana_bmlt" {
  domain_name = "*.bmltenabled.org"
  subject_alternative_names = [
    "*.aws.bmlt.app",
  ]
  validation_method = "DNS"
}

resource "aws_acm_certificate_validation" "mvana_bmlt" {
  certificate_arn = aws_acm_certificate.mvana_bmlt.arn
  validation_record_fqdns = [
    aws_route53_record.mvana_bmlt_validation["*.aws.bmlt.app"].fqdn,
    aws_route53_record.mvana_bmltenabled_validation["*.bmltenabled.org"].fqdn
  ]
}

resource "aws_route53_record" "mvana_bmlt_validation" {
  for_each = {
    for dvo in aws_acm_certificate.mvana_bmlt.domain_validation_options : dvo.domain_name => {
      name    = dvo.resource_record_name
      record  = dvo.resource_record_value
      type    = dvo.resource_record_type
      zone_id = aws_route53_zone.bmlt.zone_id
    } if dvo.domain_name == "*.aws.bmlt.app"
  }
  name    = each.value.name
  records = [each.value.record]
  type    = each.value.type
  zone_id = each.value.zone_id
  ttl     = 60
}


resource "aws_route53_record" "mvana_bmltenabled_validation" {
  provider = aws.bmlt
  for_each = {
    for dvo in aws_acm_certificate.mvana_bmlt.domain_validation_options : dvo.domain_name => {
      name    = dvo.resource_record_name
      record  = dvo.resource_record_value
      type    = dvo.resource_record_type
      zone_id = data.aws_route53_zone.bmltenabled.zone_id
    } if dvo.domain_name == "*.bmltenabled.org"
  }
  name    = each.value.name
  records = [each.value.record]
  type    = each.value.type
  zone_id = each.value.zone_id
  ttl     = 60
}

data "aws_route53_zone" "bmltenabled" {
  provider = aws.bmlt
  name     = "bmltenabled.org."
}
