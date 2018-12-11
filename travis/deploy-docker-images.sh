#!/bin/bash

tag_and_push() {
    SOURCE_IMAGE=$1
    TARGET_IMAGE_NAME=$2

    TARGET_IMAGE1="$TARGET_IMAGE_NAME:$TRAVIS_COMMIT"

    if [ ! -z $TRAVIS_TAG ]
    then
        TARGET_IMAGE2="$TARGET_IMAGE_NAME:$TRAVIS_TAG"
    elif [ "$TRAVIS_BRANCH" == "unstable" ]
    then
        TARGET_IMAGE2="$TARGET_IMAGE_NAME:unstable"
    elif [ "$TRAVIS_BRANCH" == "master" ]
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

echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
tag_and_push "bmlt-root-server:$TRAVIS_COMMIT" bmltenabled/bmlt-root-server
tag_and_push "bmlt-root-server-sample-db:$TRAVIS_COMMIT" bmltenabled/bmlt-root-server-sample-db
