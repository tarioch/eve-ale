verify: checkstyle

checkstyle:
	@./vendor/bin/phpcs -s -p --standard=phpcs_rules.xml --extensions=php src || exit 2

.PHONY: verify checkstyle
