verify: checkstyle detectmess test

test:
	@./vendor/bin/phpunit || exit 4

checkstyle:
	@./vendor/bin/phpcs -s -p --standard=phpcs_rules.xml --extensions=php src || exit 2

detectmess:
	@./vendor/bin/phpmd src text phpmd_rules.xml || exit 3

composer_get:
	php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"

composer_update:
	@./composer.phar self-update || exit 1
	@./composer.phar update || exit 1

composer_install:
	@./composer.phar install || exit 1

.PHONY: verify test checkstyle detectmess composer_update
