#!/usr/bin/env bash

username="$1"
password="$2"

curl \
    -d '{"username": "'"${username}"'", "password": "'"${password}"'"}' \
    -H 'Content-Type: application/json' \
    -X POST \
    -s \
    http://0.0.0.0:8000/main_server/api/v1/auth/token | jq .
