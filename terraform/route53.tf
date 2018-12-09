data "aws_route53_zone" "bmlt" {
  name = "aws.bmlt.app."
}

resource "aws_route53_record" "bmlt" {
  zone_id = "${data.aws_route53_zone.bmlt.id}"
  name    = "latest.${data.aws_route53_zone.bmlt.name}"
  type    = "A"

  alias {
    name                   = "${aws_alb.bmlt.dns_name}"
    zone_id                = "${aws_alb.bmlt.zone_id}"
    evaluate_target_health = true
  }
}
