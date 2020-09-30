# Testing

The process isn't (currently) very portable - I will get to it eventually...

## "Browser" tests

This test suite is intended to run against a fresh VVV install:

* The site must be accessible at `http://local.wordpress.test`.
* There must be a page with the slug `sample-page`.
* Phpunit must be run from within the vm to have access to wp-cli.
* All example plugins within the `tests/examples` directory MUST be symlinked into the site plugin directory.
