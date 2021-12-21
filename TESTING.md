# Testing

PHPUnit is used for unit testing and rough integration testing. Cypress is used for more thorough end-to-end testing.

## Cypress Details

The cypress test suite assumes a fresh VVV install available at `http://one.wordpress.test`.

There must be a page with the slug `sample-page`.

The `cfw-test-helper` plugin within the `tests/fixtures/plugins` directory must be symlinked or copied into the site plugin directory and activated. Before you will be able to activate the plugin you must run `composer install` and set the WP environment type to something other than `production` using the `WP_ENVIRONMENT_TYPE` environment variable or `WP_ENVIRONMENT_TYPE` constant.

## IMPORTANT!!!

Do not run the `cfw-test-helper` plugin on a machine that is publicly accessible. It allows `clockwork-for-wp` to be configured on-the-fly via query string params without any authentication or authorization checks. It could be used by attackers to expose sensitive information about the server and/or WordPress install.
