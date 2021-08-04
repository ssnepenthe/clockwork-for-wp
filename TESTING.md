# Testing

The process isn't (currently) very portable - I will get to it eventually...

## "Browser" tests

This test suite is intended to run against a fresh VVV install:

* The site must be accessible at `http://one.wordpress.test`.
* There must be a page with the slug `sample-page`.
* The `cfw-configure-over-http` plugin within the `tests/Browser/fixtures/plugins` directory must be symlinked into the site plugin directory.
* The WP environment type must be set to something other than production and the `cfw-configure-over-http` plugin must be active.
