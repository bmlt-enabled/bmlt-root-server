resource "aws_route53_zone" "bmlt" {
  name = "aws.bmlt.app"
}

resource "aws_route53_record" "bmlt_latest" {
  zone_id = aws_route53_zone.bmlt.id
  name    = "latest.${aws_route53_zone.bmlt.name}"
  type    = "A"


  alias {
    name                   = data.aws_lb.main.dns_name
    zone_id                = data.aws_lb.main.zone_id
    evaluate_target_health = true
  }
}

resource "aws_route53_record" "bmlt_unstable" {
  zone_id = aws_route53_zone.bmlt.id
  name    = "unstable.${aws_route53_zone.bmlt.name}"
  type    = "A"

  alias {
    name                   = data.aws_lb.main.dns_name
    zone_id                = data.aws_lb.main.zone_id
    evaluate_target_health = true
  }
}
