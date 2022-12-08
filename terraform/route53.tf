resource "aws_route53_record" "bmlt_latest" {
  zone_id = data.aws_route53_zone.aws_bmlt_app.id
  name    = "latest.${data.aws_route53_zone.aws_bmlt_app.name}"
  type    = "A"

  alias {
    name                   = data.aws_lb.main.dns_name
    zone_id                = data.aws_lb.main.zone_id
    evaluate_target_health = true
  }
}

resource "aws_route53_record" "bmlt_unstable" {
  zone_id = data.aws_route53_zone.aws_bmlt_app.id
  name    = "unstable.${data.aws_route53_zone.aws_bmlt_app.name}"
  type    = "A"

  alias {
    name                   = data.aws_lb.main.dns_name
    zone_id                = data.aws_lb.main.zone_id
    evaluate_target_health = true
  }
}
