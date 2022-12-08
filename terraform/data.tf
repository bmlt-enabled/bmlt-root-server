data "aws_elb_service_account" "main" {}

data "aws_acm_certificate" "bmltenabled_org" {
  domain      = "*.bmltenabled.org"
  statuses    = ["ISSUED"]
  most_recent = true
}

data "aws_route53_zone" "aws_bmlt_app" {
  name = "aws.bmlt.app."
}

data "aws_security_group" "ecs_clusters" {
  name   = "ecs-clusters"
  vpc_id = data.aws_vpc.main.id
}

data "aws_security_group" "rds_mysql" {
  name   = "rds-mysql"
  vpc_id = data.aws_vpc.main.id
}

data "aws_iam_instance_profile" "ecs" {
  name = "bmlt-ecs"
}

data "aws_lb" "main" {
  name = "bmlt"
}

data "aws_lb_listener" "main_443" {
  load_balancer_arn = data.aws_lb.main.arn
  port              = 443
}

data "aws_vpc" "main" {
  filter {
    name   = "tag:Name"
    values = ["bmlt"]
  }
}

data "aws_subnets" "main" {
  filter {
    name   = "vpc-id"
    values = [data.aws_vpc.main.id]
  }
}

data "aws_subnet" "main" {
  for_each = toset(data.aws_subnets.main.ids)
  id       = each.value
}

data "aws_db_subnet_group" "bmlt" {
  name = "bmlt"
}

data "aws_secretsmanager_secret" "docker" {
  name = "docker"
}

data "aws_iam_role" "ec2_assume" {
  name = "ec2-assume"
}

data "cloudinit_config" "cluster" {
  part {
    content_type = "text/x-shellscript"
    content      = <<EOF
#!/usr/bin/env bash

echo ECS_CLUSTER=${local.cluster_name} >> /etc/ecs/ecs.config

# Install awslogs and the jq
yum install -y awslogs jq

# Inject the CloudWatch Logs configuration file contents
cat > /etc/awslogs/awslogs.conf <<- EOF
[general]
state_file = /var/lib/awslogs/agent-state

[/var/log/dmesg]
file = /var/log/dmesg
log_group_name = ${local.cluster_name}/var/log/dmesg
log_stream_name = ${local.cluster_name}/{container_instance_id}

[/var/log/messages]
file = /var/log/messages
log_group_name = ${local.cluster_name}/var/log/messages
log_stream_name = ${local.cluster_name}/{container_instance_id}
datetime_format = %b %d %H:%M:%S

[/var/log/docker]
file = /var/log/docker
log_group_name = ${local.cluster_name}/var/log/docker
log_stream_name = ${local.cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%S.%f

[/var/log/ecs/ecs-init.log]
file = /var/log/ecs/ecs-init.log.*
log_group_name = ${local.cluster_name}/var/log/ecs/ecs-init.log
log_stream_name = ${local.cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%SZ

[/var/log/ecs/ecs-agent.log]
file = /var/log/ecs/ecs-agent.log.*
log_group_name = ${local.cluster_name}/var/log/ecs/ecs-agent.log
log_stream_name = ${local.cluster_name}/{container_instance_id}
datetime_format = %Y-%m-%dT%H:%M:%SZ

[/var/log/ecs/audit.log]
file = /var/log/ecs/audit.log.*
log_group_name = ${local.cluster_name}/var/log/ecs/audit.log
log_stream_name = ${local.cluster_name}/{container_instance_id}
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

data "aws_ami" "ecs" {
  most_recent = true

  owners = ["amazon"]

  filter {
    name   = "name"
    values = ["amzn2-ami-ecs-hvm-*-x86_64-ebs"]
  }
}

data "aws_key_pair" "this" {
  key_name           = "bmlt"
  include_public_key = true
}

data "aws_iam_role" "ecs_task" {
  name = "ecs-task"
}

data "aws_iam_role" "ecs_service" {
  name = "ecs-service"
}
