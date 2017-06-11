test-get: prepare
	blackfire run --samples=100 php get.php
test-get-cache: prepare
	blackfire run --samples=100 php get-cache.php
test-get-object: prepare
	blackfire run --samples=100 php get-object.php
test-factory: prepare
	blackfire run --samples=100 php factory.php
test-call: prepare
	blackfire run --samples=100 php call.php

prepare: clean vendor validate warmup
	@composer install --classmap-authoritative

warmup:
	@set -e
	@php call.php
	@php factory.php
	@php get.php
	@php get-cache.php
	@php get-object.php

clean:
	@rm -f tmp/*

vendor: composer.json
	composer update --classmap-authoritative

validate:
	@set -e
	@php -r "if (extension_loaded('xdebug')) { echo 'XDebug must not be loaded' . PHP_EOL; exit(1); }"
