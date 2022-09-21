#!/usr/bin/env bash

token="$1"

curl \
    -H "Authorization: Bearer ${token}" \
    -H 'Content-Type: application/json' \
    -X POST \
    -s \
    http://0.0.0.0:8000/main_server/api/v1/auth/refresh | jq .
