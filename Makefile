.PHONY:	targets test listsuite
all: test

targets:
	@grep '^\w.*:' Makefile

test:
	vendor/bin/phpunit

listsuites:
	vendor/bin/phpunit --list-suites
