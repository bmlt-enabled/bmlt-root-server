#!/bin/bash
set -o xtrace
set -e

/bin/bash /tmp/write-config.sh
/usr/bin/php /var/www/html/main_server/artisan aggregator:ImportRootServers
