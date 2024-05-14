resource "aws_ecs_cluster" "main" {
  name = "bmlt"
}

resource "aws_autoscaling_group" "cluster" {
  name                = local.cluster_name
  vpc_zone_identifier = data.aws_subnets.main.ids
  min_size            = 1
  max_size            = 1
  desired_capacity    = 1

  launch_template {
    id      = aws_launch_template.bmlt_cluster.id
    version = "$Latest"
  }

  dynamic "tag" {
    for_each = [
      {
        key   = "Name"
        value = "bmlt-ecs"
      },
      {
        key   = "application"
        value = "bmlt"
      },
      {
        key   = "environment"
        value = "production"
      },
    ]
    content {
      key                 = tag.value.key
      value               = tag.value.value
      propagate_at_launch = true
    }
  }
}


locals {
  cluster_name = aws_ecs_cluster.main.name
}

resource "aws_launch_template" "bmlt_cluster" {
  name_prefix            = local.cluster_name
  image_id               = data.aws_ami.ecs.image_id
  instance_type          = "t3a.small"
  key_name               = aws_key_pair.main.key_name
  user_data              = data.cloudinit_config.cluster.rendered
  update_default_version = true

  iam_instance_profile {
    name = data.aws_iam_instance_profile.ecs.name
  }

  network_interfaces {
    associate_public_ip_address = true
    security_groups             = [data.aws_security_group.ecs_clusters.id]
  }

  block_device_mappings {
    device_name = "/dev/xvda"

    ebs {
      volume_size           = 30
      volume_type           = "gp3"
      delete_on_termination = true
    }
  }

  tag_specifications {
    resource_type = "instance"
    tags = {
      Name        = "bmlt-ecs"
      application = "true"
      environment = "production"
    }
  }

  tag_specifications {
    resource_type = "volume"
    tags = {
      Name        = "bmlt-ecs"
      application = "true"
      environment = "production"
    }
  }

  lifecycle {
    create_before_destroy = true
    ignore_changes        = [image_id]
  }
}
