#!/usr/bin/env bash

composer lint-fix
php -d pcov.directory='.' vendor/bin/phpunit --coverage-html build --coverage-text
