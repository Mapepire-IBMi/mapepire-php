# developement shortcuts

.PHONY:	all test

all: test

test:
	vendor/bin/phpunit

listsuites:
	vendor/bin/phpunit --list-suites
