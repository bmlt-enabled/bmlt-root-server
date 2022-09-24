COMMIT := $(shell git rev-parse --short=8 HEAD)
BASE_IMAGE := bmltenabled/bmlt-root-server-base
BASE_IMAGE_TAG := latest
BASE_IMAGE_BUILD_TAG := $(COMMIT)-$(shell date +%s)
CROUTON_JS := src/legacy/client_interface/html/croutonjs/crouton.js
VENDOR_AUTOLOAD := src/vendor/autoload.php
ZIP_FILE := build/bmlt-root-server.zip
ifeq ($(CI)x, x)
	DOCKERFILE := Dockerfile-debug
	IMAGE := rootserver
	TAG := local
	COMPOSER_ARGS :=
	COMPOSER_PREFIX := docker run --pull=always -t --rm -v $(shell pwd):/code -w /code $(BASE_IMAGE):$(BASE_IMAGE_TAG)
	LINT_PREFIX := docker run -t --rm -v $(shell pwd):/code -w /code $(IMAGE):$(TAG)
	TEST_PREFIX := docker run -e XDEBUG_MODE=coverage,debug -t --rm -v $(shell pwd)/src:/var/www/html/main_server -v $(shell pwd)/docker/test-auto-config.inc.php:/var/www/html/auto-config.inc.php -w /var/www/html/main_server --network host $(IMAGE):$(TAG)
else
	DOCKERFILE := Dockerfile
	IMAGE := bmltenabled/bmlt-root-server
	TAG := 3.0.0-$(COMMIT)
	ifeq ($(strip $(GITHUB_REF_NAME)),main)
		TAG := latest
	endif
	ifeq ($(strip $(GITHUB_REF_NAME)),unstable)
		TAG := unstable
	endif
	COMPOSER_ARGS := --classmap-authoritative
	ifeq ($(DEV)x, x)
		COMPOSER_ARGS := $(COMPOSER_ARGS) --no-dev
	endif
	COMPOSER_PREFIX :=
	LINT_PREFIX :=
	TEST_PREFIX := cd src &&
endif

help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

$(VENDOR_AUTOLOAD):
	$(COMPOSER_PREFIX) composer install --working-dir=src $(COMPOSER_ARGS)

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

.PHONY: zip
zip: $(ZIP_FILE) ## Builds zip file

.PHONY: docker
docker: zip ## Builds Docker Image
	docker build --pull -f docker/$(DOCKERFILE) . -t $(IMAGE):$(TAG)

.PHONY: docker-push
docker-push: ## Pushes docker image to Dockerhub
	docker push $(IMAGE):$(TAG)

.PHONY: dev
dev: zip ## Docker Compose Up
	docker-compose -f docker/docker-compose.yml up --build

.PHONY: test
test:  ## Runs PHP Tests
	$(TEST_PREFIX) vendor/phpunit/phpunit/phpunit
# 	$(TEST_PREFIX) vendor/phpunit/phpunit/phpunit tests/Feature/Admin/TokenTest.php
# 	$(TEST_PREFIX) vendor/phpunit/phpunit/phpunit --filter testLogout tests/Feature/Admin/TokenTest.php

.PHONY: coverage
coverage:  ## Generates HTML Coverage Report
	$(TEST_PREFIX) vendor/phpunit/phpunit/phpunit --coverage-html tests/reports/coverage

.PHONY: coverage-serve
coverage-serve:  ## Serves HTML Coverage Report
	python3 -m http.server 8100 --directory src/tests/reports/coverage

.PHONY: lint
lint:  ## PHP Lint
	$(LINT_PREFIX) src/vendor/squizlabs/php_codesniffer/bin/phpcs

.PHONY: lint-fix
lint-fix:  ## PHP Lint Fix
	$(LINT_PREFIX) src/vendor/squizlabs/php_codesniffer/bin/phpcbf

.PHONY: docker-publish-base
docker-publish-base:  ## Builds Base Docker Image
	docker buildx build --platform linux/amd64,linux/arm64/v8 -f docker/Dockerfile-base docker/ -t $(BASE_IMAGE):$(BASE_IMAGE_TAG) --push

.PHONY: mysql
mysql:  ## Runs mysql cli in mysql container
	docker exec -it docker_db_1 mysql -u root -prootserver rootserver

.PHONY: bash
bash:  ## Runs bash shell in apache container
	docker exec -it -w /var/www/html/main_server docker_bmlt_1 bash

.PHONY: clean
clean:  ## Clean build
	rm -rf src/legacy/client_interface/html/croutonjs
	rm -rf src/vendor
	rm -rf build
