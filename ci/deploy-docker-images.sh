#!/bin/bash

tag_and_push() {
    SOURCE_IMAGE=$1
    TARGET_IMAGE_NAME=$2

    TARGET_IMAGE1="$TARGET_IMAGE_NAME:${GITHUB_SHA}"

    if [ "$GITHUB_REF_TYPE" == "tag" ]
    then
        TARGET_IMAGE2="$TARGET_IMAGE_NAME:${GITHUB_REF##*/}"
    elif [ "$GITHUB_REF_NAME" == "unstable" ]
    then
        TARGET_IMAGE2="$TARGET_IMAGE_NAME:unstable"
    elif [ "$GITHUB_REF_NAME" == "master" ]
    then
        TARGET_IMAGE2="$TARGET_IMAGE_NAME:latest"
    fi

    docker tag $SOURCE_IMAGE $TARGET_IMAGE1
    docker push $TARGET_IMAGE1

    if [ ! -z $TARGET_IMAGE2 ]
    then
        docker tag $SOURCE_IMAGE $TARGET_IMAGE2
        docker push $TARGET_IMAGE2
    fi
}

aws ecr-public get-login-password --region us-east-1 | docker login --username AWS --password-stdin public.ecr.aws/t5y4k5q3
tag_and_push "bmlt-root-server:${GITHUB_SHA}" public.ecr.aws/t5y4k5q3/bmlt-root-server
tag_and_push "bmlt-root-server-sample-db:${GITHUB_SHA}" public.ecr.aws/t5y4k5q3/bmlt-root-server-sample-db
