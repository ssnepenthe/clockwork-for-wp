#!/usr/bin/env bash

npx wp-env start
npx wp-env clean all
npx wp-env run tests-wordpress "chmod -c ugo+w /var/www/html"
npx wp-env run tests-wordpress "mkdir -p /var/www/html/wp-content/cfw-data"
npx wp-env run tests-wordpress "chmod -c ugo+w /var/www/html/wp-content/cfw-data"
npx wp-env run tests-cli "wp rewrite structure '/%postname%/' --hard"
# @todo composer flags? --ansi --no-interaction --no-progress --prefer-dist
npx wp-env run composer install
