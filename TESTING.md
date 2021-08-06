# Testing

The process isn't (currently) very portable - I will get to it eventually...

There are three test suites: `unit`, `integration`, and `browser`.

They can all be run at once using `./vendor/bin/phpunit` or individually using `./vendor/bin/phpunit --testsuite {test suite name}`.

## Browser tests

This is effectively an end-to-end test suite. By default it assumes a fresh VVV install available at `http://one.wordpress.test`.

This can be overridden by creating a file called `baseuri` within the `tests` directory with its only contents set to base uri where your test site is accessible (e.g. `https://two.wordpress.test`).

There must be a page with the slug `sample-page`.

The `cfw-configure-over-http` plugin within the `tests/Browser/fixtures/plugins` directory must be symlinked or copied into the site plugin directory and activated. Before you will be able to activate the plugin you must set the WP environment type to something other than `production`.

## IMPORTANT!!!

Do not run the `cfw-configure-over-http` plugin on a machine that is exposed to the internet at large. It allows `clockwork-for-wp` to be configured on-the-fly via query string params without any authentication or authorization checks. It could be used by attackers to expose sensitive information about the server and/or WordPress install.
