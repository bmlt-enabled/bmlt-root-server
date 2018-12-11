tag() {
    SOURCE_IMAGE=$1
    TARGET_IMAGE=$2

    if [ ! -z $TRAVIS_TAG ]
    then
        TARGET_IMAGE="$TARGET_IMAGE:$TRAVIS_TAG"
    else
        TARGET_IMAGE="$TARGET_IMAGE:latest"
    fi

    docker tag $SOURCE_IMAGE $TARGET_IMAGE

    echo $TARGET_IMAGE
}


push() {
    echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
    docker push $1
}

push $(tag "bmlt-root-server:$TRAVIS_COMMIT" bmlt-root-server)
push $(tag "bmlt-root-server-sample-db:$TRAVIS_COMMIT" bmlt-root-server-sample-db)
