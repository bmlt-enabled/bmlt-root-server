resource "aws_ecs_cluster" "aggregator" {
  name = "aggregator"
}

resource "aws_autoscaling_group" "aggregator_cluster" {
  name                = local.aggregator_cluster_name
  vpc_zone_identifier = data.aws_subnets.main.ids
  min_size            = 2
  max_size            = 2
  desired_capacity    = 2

  launch_template {
    id      = aws_launch_template.aggregator_cluster.id
    version = "$Latest"
  }

  dynamic "tag" {
    for_each = [
      {
        key   = "Name"
        value = "aggregator"
      },
      {
        key   = "application"
        value = "aggregator"
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
  aggregator_cluster_name = aws_ecs_cluster.aggregator.name
}

resource "aws_launch_template" "aggregator_cluster" {
  name_prefix   = local.aggregator_cluster_name
  image_id      = data.aws_ami.ecs.image_id
  instance_type = "t3a.small"
  key_name      = aws_key_pair.main.key_name
  user_data     = data.cloudinit_config.aggregator_cluster.rendered

  iam_instance_profile {
    name = aws_iam_instance_profile.cluster.name
  }

  network_interfaces {
    associate_public_ip_address = true
    security_groups             = [aws_security_group.aggregator_cluster.id]
  }

  tag_specifications {
    resource_type = "instance"
    tags = {
      Name        = "aggregator"
      application = "true"
      environment = "production"
    }
  }

  tag_specifications {
    resource_type = "volume"
    tags = {
      Name        = "aggregator"
      application = "true"
      environment = "production"
    }
  }

  lifecycle {
    create_before_destroy = true
    ignore_changes        = [image_id]
  }
}

data "cloudinit_config" "aggregator_cluster" {
  part {
    content_type = "text/x-shellscript"
    content      = <<EOF
#!/usr/bin/env bash

echo ECS_CLUSTER=${local.aggregator_cluster_name} >> /etc/ecs/ecs.config

# Install awslogs and the jq
yum install -y awslogs jq

# Inject the CloudWatch Logs configuration file contents
cat > /etc/awslogs/awslogs.conf <<- EOF
[general]
state_file = /var/lib/awslogs/agent-state

[/var/log/dmesg]
file = /var/log/dmesg
log_group_name = ${local.aggregator_cluster_name}/var/log/dmesg
log_stream_name = ${local.aggregator_cluster_name}/{container_instance_id}

[/var/log/messages]
file = /var/log/messages
log_group_name = ${local.aggregator_cluster_name}/var/log/messages
log_stream_name = ${local.aggregator_cluster_name}/{container_instance_id}
datetime_format = %b %d %H:%M:%S

[/var/log/docker]
file = /var/log/docker
log_group_name = ${local.aggregator_cluster_name}/var/log/docker
log_stream_name = ${local.aggregator_cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%S.%f

[/var/log/ecs/ecs-init.log]
file = /var/log/ecs/ecs-init.log.*
log_group_name = ${local.aggregator_cluster_name}/var/log/ecs/ecs-init.log
log_stream_name = ${local.aggregator_cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%SZ

[/var/log/ecs/ecs-agent.log]
file = /var/log/ecs/ecs-agent.log.*
log_group_name = ${local.aggregator_cluster_name}/var/log/ecs/ecs-agent.log
log_stream_name = ${local.aggregator_cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%SZ

[/var/log/ecs/audit.log]
file = /var/log/ecs/audit.log.*
log_group_name = ${local.aggregator_cluster_name}/var/log/ecs/audit.log
log_stream_name = ${local.aggregator_cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%SZ
EOF
  }

  part {
    content_type = "text/x-shellscript"
    content      = <<BSEOF
#!/usr/bin/env bash
# Write the awslogs bootstrap script to /usr/local/bin/bootstrap-awslogs.sh
cat > /usr/local/bin/bootstrap-awslogs.sh <<- 'EOF'
#!/usr/bin/env bash
exec 2>>/var/log/ecs/cloudwatch-logs-start.log
set -x
until curl -s http://localhost:51678/v1/metadata
do
	sleep 1
done

# Set the region to send CloudWatch Logs data to (the region where the container instance is located)
cp /etc/awslogs/awscli.conf /etc/awslogs/awscli.conf.bak
region=$(curl -s 169.254.169.254/latest/dynamic/instance-identity/document | jq -r .region)
sed -i -e "s/region = .*/region = $region/g" /etc/awslogs/awscli.conf

# Grab the cluster and container ip address of the node
cluster=$(curl -s http://localhost:51678/v1/metadata | jq -r '. | .Cluster')
container_instance_id=$(curl 169.254.169.254/latest/meta-data/local-ipv4)

# Replace the cluster name and container instance ID placeholders with the actual values
cp /etc/awslogs/awslogs.conf /etc/awslogs/awslogs.conf.bak
sed -i -e "s/{cluster}/$cluster/g" /etc/awslogs/awslogs.conf
sed -i -e "s/{container_instance_id}/$container_instance_id/g" /etc/awslogs/awslogs.conf
BSEOF
  }

  part {
    content_type = "text/x-shellscript"
    content      = <<LSEOF
#!/usr/bin/env bash
# Write the bootstrap-awslogs systemd unit file to /etc/systemd/system/bootstrap-awslogs.service
cat > /etc/systemd/system/bootstrap-awslogs.service <<- EOF
[Unit]
Description=Bootstrap awslogs agent
Requires=ecs.service
After=ecs.service
Before=awslogsd.service
[Service]
Type=oneshot
RemainAfterExit=yes
ExecStart=/usr/local/bin/bootstrap-awslogs.sh
[Install]
WantedBy=awslogsd.service
EOF
LSEOF
  }

  part {
    content_type = "text/x-shellscript"
    content      = <<SSEOF
#!/usr/bin/env bash

# Start logs
chmod +x /usr/local/bin/bootstrap-awslogs.sh
systemctl daemon-reload
systemctl enable bootstrap-awslogs.service
systemctl enable awslogsd.service
systemctl start awslogsd.service --no-block
SSEOF
  }
}

resource "aws_security_group" "aggregator_cluster" {
  description = "controls direct access to aggregator cluster container instances"
  vpc_id      = data.aws_vpc.main.id
  name        = local.aggregator_cluster_name

  ingress {
    protocol  = "tcp"
    from_port = 32768
    to_port   = 61000

    security_groups = [
      aws_security_group.ecs_http_load_balancers.id,
      "sg-0573eb4a1df88751a", # the tomato alb
    ]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}
