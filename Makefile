lint:
	main_server/vendor/squizlabs/php_codesniffer/bin/phpcs --warning-severity=6 --standard=PSR2 --ignore=vendor --extensions=php --report=full main_server

lint-fix:
	main_server/vendor/squizlabs/php_codesniffer/bin/phpcbf --warning-severity=6 --standard=PSR2 --ignore=vendor --extensions=php --report=full main_server

deps-dev:
	composer install --dev

deps:
	composer install --no-dev
	npm install \
	  && mkdir -p main_server/client_interface/html/croutonjs \
	  && cp node_modules/@bmlt-enabled/croutonjs/*.css main_server/client_interface/html/croutonjs/ \
	  && cp node_modules/@bmlt-enabled/croutonjs/*.js main_server/client_interface/html/croutonjs/ \
	  && cp -r node_modules/@bmlt-enabled/croutonjs/templates main_server/client_interface/html/croutonjs/templates \
	  && cp -r node_modules/@bmlt-enabled/croutonjs/fonts main_server/client_interface/html/croutonjs/fonts

clean:
	rm -rf main_server/vendor
	rm -rf node_modules
	rm -rf main_server/client_interface/html/croutonjs

.PHONY: lint deps deps-dev clean
