#!/usr/bin/env bash

previously_started="no"

npx wp-env install-path > /dev/null 2>&1

test $? -eq 0 && previously_started="yes"

echo "Starting WordPress development environment"

npx wp-env start

if [[ $previously_started == 'no' ]]; then
    echo "Performing additional set up tasks..."

    npx wp-env clean all
    npx wp-env run tests-wordpress "chmod -c ugo+w /var/www/html"
    npx wp-env run tests-wordpress "mkdir -p /var/www/html/wp-content/cfw-data"
    npx wp-env run tests-wordpress "chmod -c ugo+w /var/www/html/wp-content/cfw-data"
    npx wp-env run tests-cli "wp rewrite structure '/%postname%/' --hard"
    # @todo composer flags? --ansi --no-interaction --no-progress --prefer-dist
    npx wp-env run composer install
fi
