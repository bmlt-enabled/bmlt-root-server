COMMIT := $(shell git rev-parse --short=8 HEAD)

ifeq ($(CI)x, x)
	DOCKERFILE := Dockerfile-debug
	IMAGE := rootserver
	TAG := local
	DOCKER_ARGS := -t --rm
	COMPOSER_ARGS :=
	ZIP_NAME := bmlt-root-server-3.0.0-build$(COMMIT).zip
else
	DOCKERFILE := Dockerfile
	IMAGE := public.ecr.aws/bmlt/bmlt-root-server
	TAG := 3.0.0-$(COMMIT)
	DOCKER_ARGS := -t
	COMPOSER_ARGS :=  --no-dev --classmap-authoritative
	ZIP_NAME := bmlt-root-server-3.0.0-build$(GITHUB_RUN_NUMBER)-$(GITHUB_SHA).zip
endif
BASE_IMAGE := public.ecr.aws/bmlt/bmlt-root-server-base
BASE_IMAGE_TAG := 1.1.0
BASE_IMAGE_BUILD_TAG := $(COMMIT)-$(shell date +%s)
CROUTON_JS := src/legacy/client_interface/html/croutonjs/crouton.js
VENDOR_AUTOLOAD := src/vendor/autoload.php
ZIP_FILE := build/bmlt-root-server.zip

help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

$(VENDOR_AUTOLOAD):
	docker run $(DOCKER_ARGS) -v $(shell pwd)/src:/app -w /app $(BASE_IMAGE):$(BASE_IMAGE_TAG) composer install $(COMPOSER_ARGS)

$(CROUTON_JS):
	curl -sLO https://github.com/bmlt-enabled/crouton/releases/latest/download/croutonjs.zip
	mkdir -p src/legacy/client_interface/html/croutonjs
	unzip croutonjs.zip -d src/legacy/client_interface/html/croutonjs
	rm -f croutonjs.zip
	rm -f src/legacy/client_interface/html/croutonjs/*.html
	rm -f src/legacy/client_interface/html/croutonjs/*.json
	rm -rf src/legacy/client_interface/html/croutonjs/examples

$(ZIP_FILE): $(VENDOR_AUTOLOAD) $(CROUTON_JS)
	mkdir -p build
	cp -r src build/main_server
	cd build && zip -r $(shell basename $(ZIP_FILE)) main_server
	rm -rf build/main_server

.PHONY: composer
composer: $(VENDOR_AUTOLOAD) ## Runs composer install

.PHONY: crouton
crouton: $(CROUTON_JS) ## Installs crouton

.PHONY: docker
docker: $(VENDOR_AUTOLOAD) $(CROUTON_JS) ## Builds Docker Image
	docker build -f docker/$(DOCKERFILE) . -t $(IMAGE):$(TAG)

.PHONY: zip
zip: $(ZIP_FILE) ## Builds zip file

.PHONY: docker-push
docker-push: ## Pushes docker image to ECR
	docker push $(IMAGE):$(TAG)

.PHONY: ecr-login
ecr-login: ## Authenticates to ECR
	aws ecr-public get-login-password --region us-east-1 | docker login --username AWS --password-stdin public.ecr.aws/bmlt

.PHONY: dev
dev:  ## Docker Compose Up
	docker-compose -f docker/docker-compose.yml up --build

.PHONY: clean
clean:  ## Clean build
	rm -rf src/legacy/client_interface/html/croutonjs
	rm -rf src/vendor
	rm -rf build

.PHONY: lint
lint: $(VENDOR_AUTOLOAD)  ## PHP Lint
	src/vendor/squizlabs/php_codesniffer/bin/phpcs

.PHONY: lint-fix
lint-fix: $(VENDOR_AUTOLOAD)  ## PHP Lint Fix
	src/vendor/squizlabs/php_codesniffer/bin/phpcbf

.PHONY: docker-publish-base
docker-publish-base:  ## Builds Base Docker Image
	docker build -f docker/Dockerfile-base docker/ -t $(BASE_IMAGE):$(BASE_IMAGE_BUILD_TAG)
	docker tag $(BASE_IMAGE):$(BASE_IMAGE_BUILD_TAG) $(BASE_IMAGE):$(BASE_IMAGE_TAG)
	docker push $(BASE_IMAGE):$(BASE_IMAGE_BUILD_TAG)
	docker push $(BASE_IMAGE):$(BASE_IMAGE_TAG)
