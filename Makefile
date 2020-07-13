lint:
	main_server/vendor/squizlabs/php_codesniffer/bin/phpcs --warning-severity=6 --standard=PSR2 --ignore=vendor --extensions=php --report=full main_server

lint-fix:
	main_server/vendor/squizlabs/php_codesniffer/bin/phpcbf --warning-severity=6 --standard=PSR2 --ignore=vendor --extensions=php --report=full main_server

deps:
	composer install --no-dev
	npm install \
	  && cp node_modules/@bmlt-enabled/croutonjs/crouton.css main_server/client_interface/html/crouton.css \
	  && cp node_modules/@bmlt-enabled/croutonjs/crouton.min.css main_server/client_interface/html/crouton.min.css \
	  && cp node_modules/@bmlt-enabled/croutonjs/crouton.js main_server/client_interface/html/crouton.js \
	  && cp node_modules/@bmlt-enabled/croutonjs/crouton.min.js main_server/client_interface/html/crouton.min.js \

clean:
	rm -rf main_server/vendor
	rm -rf node_modules
	rm -rf main_server/client_interface/html/crouton*.css
	rm -rf main_server/client_interface/html/crouton*.js

.PHONY: lint deps clean
