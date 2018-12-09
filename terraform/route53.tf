//data "aws_route53_zone" "jbraz" {
//  name = "jbraz.com."
//}
//resource "aws_route53_record" "bmlt" {
//  zone_id = "${data.aws_route53_zone.jbraz.id}"
//  name    = "bmlt.${data.aws_route53_zone.jbraz.name}"
//  type    = "CNAME"
//  ttl     = "300"
//  records = ["${aws_alb.bmlt.dns_name}"]
//}

