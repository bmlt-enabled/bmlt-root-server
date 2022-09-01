COMMIT := $(shell git rev-parse --short=8 HEAD)

ifeq ($(CI)x, x)
	DOCKERFILE := Dockerfile-debug
	IMAGE := rootserver
	TAG := local
	DOCKER_ARGS := -it --rm
	COMPOSER_ARGS :=
	ZIP_NAME := bmlt-root-server-3.0.0-build$(COMMIT).zip
else
	DOCKERFILE := Dockerfile
	IMAGE := public.ecr.aws/bmlt/bmlt-root-server
	TAG := 3.0.0-$(COMMIT)
	DOCKER_ARGS := -t
	COMPOSER_ARGS := --classmap-authoritative
	ZIP_NAME := bmlt-root-server-3.0.0-build$(GITHUB_RUN_NUMBER)-$(GITHUB_SHA).zip
endif

BASE_IMAGE := public.ecr.aws/bmlt/bmlt-root-server-base:latest

.PHONY: help
help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build: clean composer-install crouton-install build-docker ## Builds Full Image

build-zip: clean composer-install crouton-install zip-build ## Builds Full Zip

.PHONY: build-docker
build-docker:  ## Builds Docker Image
	docker build -f docker/$(DOCKERFILE) . -t $(IMAGE):$(TAG)

.PHONY: zip-build
zip-build:  ## Zips the build
	rm -rf build
	mkdir -p build
	cp -r src build/main_server
	cd build && zip -r $(ZIP_NAME) main_server && cd ..

.PHONY: composer-install
composer-install:  ## Composer Install
	docker run $(DOCKER_ARGS) -v $(shell pwd)/src:/app -w /app $(BASE_IMAGE) composer install $(COMPOSER_ARGS)

.PHONY: crouton-install
crouton-install:   ## Installs Crouton
	curl -sLO https://github.com/bmlt-enabled/crouton/releases/latest/download/croutonjs.zip
	mkdir -p src/legacy/client_interface/html/croutonjs
	unzip croutonjs.zip -d src/legacy/client_interface/html/croutonjs
	rm croutonjs.zip
	rm src/legacy/client_interface/html/croutonjs/*.html
	rm src/legacy/client_interface/html/croutonjs/*.json
	rm -rf src/legacy/client_interface/html/croutonjs/examples

.PHONY: dev
dev:   ## Docker Compose Up
	docker-compose -f docker/docker-compose.yml up --build

.PHONY: push
push:   ## Push Docker Image to ECR
	docker push $(IMAGE):$(TAG)

.PHONY: clean
clean:   ## Clean Build
	rm -rf src/legacy/client_interface/html/croutonjs

