COMMIT := $(shell git rev-parse --short=8 HEAD)
BASE_IMAGE := bmltenabled/bmlt-server-base
BASE_IMAGE_TAG := 8.3
BASE_IMAGE_BUILD_TAG := $(COMMIT)-$(shell date +%s)
SEMANTIC_HTML := src/public/semantic/index.html
TIMEZONE_ASSETS := src/public/timezones-1970.geojson.index.json
VENDOR_AUTOLOAD := src/vendor/autoload.php
NODE_MODULES := src/node_modules/.package-lock.json
FRONTEND := src/public/build/manifest.json
ZIP_FILE := build/bmlt-server.zip
EXTRA_DOCKER_COMPOSE_ARGS :=
COMPOSER_IN_CONTAINER := docker run --pull=always -t --rm -v '$(shell pwd)':/code -w /code $(BASE_IMAGE):$(BASE_IMAGE_TAG)
ifeq ($(CI)x, x)
	DOCKERFILE := Dockerfile-debug
	IMAGE := bmltserver
	TAG := local
	COMPOSER_ARGS :=
	NPM_FLAG := install
	COMPOSER_PREFIX := $(COMPOSER_IN_CONTAINER)
	LINT_PREFIX := docker run -t --rm -v '$(shell pwd)':/code -w /code/src $(IMAGE):$(TAG)
	TEST_PREFIX := docker run -e XDEBUG_MODE=coverage,debug -t --rm -v '$(shell pwd)/src:/var/www/html/main_server' -w /var/www/html/main_server --network host $(IMAGE):$(TAG)
	ifneq (,$(wildcard docker/docker-compose.dev.yml))
		EXTRA_DOCKER_COMPOSE_ARGS := -f docker/docker-compose.dev.yml
	endif
else
	DOCKERFILE := Dockerfile
	IMAGE := bmltenabled/bmlt-server
	TAG := 4.0.0-$(COMMIT)
	ifeq ($(strip $(GITHUB_REF_NAME)),main)
		TAG := latest
	endif
	ifeq ($(strip $(GITHUB_REF_NAME)),unstable)
		TAG := unstable
	endif
	COMPOSER_ARGS := --classmap-authoritative
	NPM_FLAG := ci
	ifeq ($(DEV)x, x)
		COMPOSER_ARGS := $(COMPOSER_ARGS) --no-dev
	endif
	COMPOSER_PREFIX :=
	LINT_PREFIX := cd src &&
	TEST_PREFIX := cd src &&
endif

# Decouple where composer runs from the dev/prod args selected by CI above, so a
# production build (CI=1) can be produced without composer installed on the host.
#   CONTAINER=1  force composer to run in the base-image container
#   CONTAINER=0  force composer to run on the host
ifeq ($(CONTAINER),1)
	COMPOSER_PREFIX := $(COMPOSER_IN_CONTAINER)
else ifeq ($(CONTAINER),0)
	COMPOSER_PREFIX :=
endif

help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

$(VENDOR_AUTOLOAD):
	$(COMPOSER_PREFIX) composer install --working-dir=src $(COMPOSER_ARGS)

$(SEMANTIC_HTML):
	curl -sLO https://github.com/bmlt-enabled/semantic-workshop/releases/latest/download/semantic-workshop.zip
	mkdir -p src/public/semantic
	unzip semantic-workshop.zip -d src/public/semantic
	rm -f semantic-workshop.zip

$(TIMEZONE_ASSETS):
	curl -sL -o src/public/timezones-1970.geojson.geo.dat https://cdn.aws.bmlt.app/geo-tz@8.1.1/data/timezones-1970.geojson.geo.dat
	curl -sL -o src/public/timezones-1970.geojson.index.json https://cdn.aws.bmlt.app/geo-tz@8.1.1/data/timezones-1970.geojson.index.json

$(NODE_MODULES):
	cd src && npm $(NPM_FLAG)

$(FRONTEND): $(NODE_MODULES)
	cd src && npm run build

$(ZIP_FILE): $(VENDOR_AUTOLOAD) $(FRONTEND) $(SEMANTIC_HTML) $(TIMEZONE_ASSETS)
	mkdir -p build
	cp -r src build/main_server
	cd build && zip -r $(shell basename $(ZIP_FILE)) main_server \
		-x main_server/.gitattributes \
		-x main_server/.gitignore \
		-x main_server/.nvmrc \
		-x main_server/.phpcs.xml \
		-x main_server/.phpstan.neon \
		-x main_server/.mcp.json \
		-x main_server/app.html \
		-x main_server/boost.json \
		-x main_server/coverage.xml \
		-x main_server/eslint.config.js \
		-x main_server/node_modules/\* \
		-x main_server/package.json \
		-x main_server/package-lock.json \
		-x main_server/phpunit.xml \
		-x main_server/.phpunit.result.cache \
		-x main_server/prettier.config.js \
		-x main_server/resources/js/\* \
		-x main_server/svelte.config.js \
		-x main_server/tests/\* \
		-x main_server/tsconfig.json \
		-x main_server/vite.config.ts
	rm -rf build/main_server

.PHONY: composer
composer: $(VENDOR_AUTOLOAD) ## Runs composer install

.PHONY: npm
npm: $(NODE_MODULES) ## Runs npm install

.PHONY: semantic
semantic: $(SEMANTIC_HTML) ## Installs semantic workshop

.PHONY:timezone
timezone: $(TIMEZONE_ASSETS) ## Installs timezone assets

.PHONY: frontend
frontend: $(FRONTEND)  ## Builds the frontend

.PHONY: zip
zip: $(ZIP_FILE) ## Builds zip file

.PHONY: docker
docker: zip ## Builds Docker Image (single-arch, local load)
	docker build --pull --build-arg PHP_VERSION=$(BASE_IMAGE_TAG) --label "org.opencontainers.image.revision=$(COMMIT)" --label "org.opencontainers.image.created=$(shell date -u +'%Y-%m-%dT%H:%M:%SZ')" -f docker/$(DOCKERFILE) . -t $(IMAGE):$(TAG)

.PHONY: docker-push
docker-push: zip ## Builds and pushes multi-arch Docker image to Dockerhub
	docker buildx build --pull --platform linux/amd64,linux/arm64/v8 --build-arg PHP_VERSION=$(BASE_IMAGE_TAG) --label "org.opencontainers.image.revision=$(COMMIT)" --label "org.opencontainers.image.created=$(shell date -u +'%Y-%m-%dT%H:%M:%SZ')" -f docker/$(DOCKERFILE) . -t $(IMAGE):$(TAG) --push

.PHONY: dev
dev: zip ## Docker Compose Up
	docker compose -f docker/docker-compose.yml $(EXTRA_DOCKER_COMPOSE_ARGS) up --build

.PHONY: test
test:  ## Runs PHP Tests
	$(TEST_PREFIX) php artisan test --parallel --recreate-databases --display-deprecations --coverage-clover coverage.xml
#	$(TEST_PREFIX) vendor/bin/phpunit tests/Feature/Admin/ServiceBodyPartialUpdateTest.php
#	$(TEST_PREFIX) vendor/bin/phpunit --filter testUpdateServiceBodyAsServiceBodyAdmin tests/Feature/Admin/ServiceBodyPartialUpdateTest.php

.PHONY: test-js
test-js:  ## Runs JavaScript tests
	cd src && npm run test

.PHONY: coverage
coverage:  ## Generates HTML Coverage Report
	$(TEST_PREFIX) vendor/phpunit/phpunit/phpunit --coverage-html tests/reports/coverage

.PHONY: coverage-serve
coverage-serve:  ## Serves HTML Coverage Report
	python3 -m http.server 8100 --directory src/tests/reports/coverage

.PHONY: generate-api-json
generate-api-json: ## Generates Open API JSON (admin + semantic docs)
	$(TEST_PREFIX) php artisan l5-swagger:generate --all

.PHONY: lint
lint:  ## PHP Lint
	$(LINT_PREFIX) vendor/squizlabs/php_codesniffer/bin/phpcs

.PHONY: lint-fix
lint-fix:  ## PHP Lint Fix
	$(LINT_PREFIX) vendor/squizlabs/php_codesniffer/bin/phpcbf

.PHONY: lint-js
lint-js:  ## JavaScript Lint
	cd src && npm run lint

.PHONY: phpstan
phpstan:  ## PHP Larastan Code Analysis
	$(LINT_PREFIX) vendor/bin/phpstan analyse -c .phpstan.neon --memory-limit=2G

.PHONY: docker-publish-base
docker-publish-base:  ## Builds Base Docker Image
	docker buildx build --platform linux/amd64,linux/arm64/v8 --label "org.opencontainers.image.revision=$(COMMIT)" --label "org.opencontainers.image.created=$(shell date -u +'%Y-%m-%dT%H:%M:%SZ')" -f docker/Dockerfile-base docker/ -t $(BASE_IMAGE):$(BASE_IMAGE_TAG) --push

.PHONY: mysql
mysql:  ## Runs mysql cli in mysql container
	docker exec -it docker-db-1 mariadb -u root -prootserver rootserver

.PHONY: bash
bash:  ## Runs bash shell in apache container
	docker exec -it -w /var/www/html/main_server docker-bmlt-1 bash

.PHONY: clean
clean:  ## Clean build
	rm -rf src/public/build
	rm -rf src/public/semantic
	rm -rf src/node_modules
	rm -rf src/vendor
	rm -rf build
