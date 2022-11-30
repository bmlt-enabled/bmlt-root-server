#!/bin/bash
set -o xtrace
set -e

/bin/bash /tmp/write-config.sh
apachectl -D FOREGROUND
