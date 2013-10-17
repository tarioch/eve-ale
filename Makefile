verify: checkstyle detectmess

checkstyle:
	@./vendor/bin/phpcs -s -p --standard=phpcs_rules.xml --extensions=php src || exit 2

detectmess:
	@./vendor/bin/phpmd src text phpmd_rules.xml || exit 3

.PHONY: verify checkstyle detectmess
