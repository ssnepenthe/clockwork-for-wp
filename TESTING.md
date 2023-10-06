# Testing

PHPUnit is used for unit testing and rough integration testing. Cypress is used for more thorough end-to-end testing.

## Cypress Details

The cypress test suite expects a running test environment provided by wp-env (localhost:8889) with some manual modifications. Refer to the script at `wp-env/start.sh` to understand what is needed. Additionally, a page with the slug `sample-page` must be present.

## IMPORTANT!!!

The `cfw-test-helper` plugin is installed and activated by default by wp-env, but it is important that this plugin is never installed on a machine that is publicly accessible. It allows `clockwork-for-wp` to be configured on-the-fly via query string params without any authentication or authorization checks. It could be used by attackers to expose sensitive information about the server and/or WordPress install.
