tag_and_push() {
    SOURCE_IMAGE=$1
    TARGET_IMAGE_NAME=$2

    if [ ! -z $TRAVIS_TAG ]
    then
        TARGET_IMAGE1="$TARGET_IMAGE_NAME:$TRAVIS_TAG"
    else
        TARGET_IMAGE1="$TARGET_IMAGE_NAME:latest"
    fi

    TARGET_IMAGE2="$TARGET_IMAGE_NAME:$TRAVIS_COMMIT"

    docker tag $SOURCE_IMAGE $TARGET_IMAGE1
    docker tag $SOURCE_IMAGE $TARGET_IMAGE2

    docker push $TARGET_IMAGE1
    docker push $TARGET_IMAGE2
}

echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
tag_and_push "bmlt-root-server:$TRAVIS_COMMIT" bmltenabled/bmlt-root-server
tag_and_push "bmlt-root-server-sample-db:$TRAVIS_COMMIT" bmltenabled/bmlt-root-server-sample-db
