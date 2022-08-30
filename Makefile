.PHONY: build-docker
build-docker:
	cd docker && docker build -t rootserver:local .

.PHONY: composer-install
composer-install: build-docker
	docker run -it -v $(shell pwd)/src:/app -w /app rootserver:local composer install

.PHONY: dev
dev:
	cd docker && docker-compose up --build
