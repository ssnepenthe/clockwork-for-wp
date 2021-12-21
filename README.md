# Clockwork for WP
A basic [Clockwork](https://underground.works/clockwork/) integration for WordPress.

## WARNINGS
DO NOT RUN THIS PLUGIN ON NON-DEVELOPMENT MACHINES - THE METADATA IT EXPOSES COULD POTENTIALLY BE USED TO TAKE OVER YOUR SITE.

This plugin is still in development and as such all APIs, configs, etc. are subject to change at any time, without warning until a proper release has been tagged.

## When To Use This Instead of Query Monitor
I would almost always recommend that you use [Query Monitor](https://wordpress.org/plugins/query-monitor/) instead of this plugin.

Query Monitor is a tool that is purpose-built for WordPress. Clockwork, on the other hand, is more suited to modern MVC style frameworks.

Query Monitor continues to support much older versions of both PHP and WordPress and collects significantly more data.

The primary reasons I would recommend using this plugin over Query Monitor would be when you want to store request metadata for later review or are trying to debug issues in a non-html context (wp-cli, cron, etc.).

## Requirements
PHP 7.1 or greater, WP 5.5 or greater and Composer.

## Installation
This plugin currently requires composer.

If your site is already using composer, add this repo as a VCS repository to your root composer.json and then install as usual (i.e. `composer require ssnepenthe/clockwork-for-wp --dev`).

Otherwise, clone this repo into your plugins directory and manually run `composer install`.

Optionally install one of the Clockwork browser extensions ([Chrome](https://chrome.google.com/webstore/detail/clockwork/dmggabnehkmmfmdffgajcflpdjlnoemp), [Firefox](https://addons.mozilla.org/en-US/firefox/addon/clockwork-dev-tools/)).

As this plugin is not meant to run on production servers, you must configure your [environment type](https://developer.wordpress.org/reference/functions/wp_get_environment_type/) to something other than 'production'.

## Usage
Once the plugin has been activated, there are three primary options for usage:

1. If you have installed the browser extension, open developer tools and browse to the Clockwork tab.

2. Open a new browser tab and navigate to the `__clockwork/app` endpoint (e.g. https://example.com/__clockwork/app).

3. Enable the browser toolbar (refer to 'configuration' below). This will provide some minimal data for every request along with a link to view more in the web view.

Note that if you are trying to debug outside of an HTML context (e.g. wp-cron, rest api, admin-ajax, wp-cli), you must use the web view instead of the browser extension and may need to enable data collection for that specific context (see 'configuration' below).

## Configuration
By default, all data sources are disabled. You can configure data sources and various other Clockwork settings using the `cfw_config_init` action from within a must-use plugin.

Your callback should accept an instance of `\Clockwork_For_Wp\Config` which can be used to change any of the configuration options found in `src/config.php`. Options are set using dot notation.

For example, consider the following must-use plugin at `wp-content/mu-plugins/cfw-config.php`:

```php
\add_action( 'cfw_config_init', function( \Clockwork_For_Wp\Config $config ) {
    // Disables the Clockwork webapp.
    $config->set( 'web', false );

    // Enables the WP_Rewrite data source.
    $config->set( 'data_sources.wp_rewrite.enabled', true );

    // Sets the metadata expiration to one day (in minutes).
    $config->set( 'storage.expiration', 60 * 24 );
} );
```

## Data source requirements
Some data sources have special requirements for use:

### wpdb
In order to use the wpdb data source the SAVEQUERIES constant must be defined and truthy.

https://wordpress.org/support/article/debugging-in-wordpress/#savequeries

### xdebug
In order to use the xdebug data source the xdebug extension must be loaded.

### errors
By default the errors data source will log all errors that occur after the plugin has loaded as well as the last error that occurred before the plugin loaded.

If you want to capture earlier errors, you can manually register the clockwork error handler by requiring the "initialize-error-logger.php" file early on in the WordPress bootstrap (e.g. from a must-use plugin).

## WP-CLI
If you would like to collect WP-CLI output, it can be beneficial (but not necessary) to add the "initialize-wp-cli-logger.php" file to the list of requires in your [WP-CLI config](https://make.wordpress.org/cli/handbook/references/config/) to ensure as much output as possible is captured.

## PHP-Scoper
Depending on how your site is configured to use composer, you may run into some issues with dependency conflicts. These should generally be resolvable by scoping the plugin dependencies with PHP-Scoper.

To do so, you will need to install three tools:

* [PHP-Scoper](https://github.com/humbug/php-scoper)
* [PHP-Scoper Prefix Remover](https://github.com/pxlrbt/php-scoper-prefix-remover)
* [WordPress Stubs](https://github.com/php-stubs/wordpress-stubs)

Refer to the respective documentation for installation instructions for each package, but installing as global composer dependencies is probably the simplest method (e.g. `composer global require humbug/php-scoper`, etc.).

Once installed, adapt the following:

```sh
# Clone this repo into a temporary directory.
cd ~/code
git clone git@github.com:ssnepenthe/clockwork-for-wp.git && cd clockwork-for-wp

# Install dependencies.
composer install --no-dev --classmap-authoritative

# Run php-scoper.
# WP_STUB_FILE variable should point to the wordpress-stubs package installed previously. If the first character is "~" it will be replaced with $_SERVER['HOME'].
# Optionally set EXCLUDE_PSR=1 to prevent scoping psr dependencies (psr/container and psr/log).
# Depending on server configuration, you may or may not need to adjust memory limit as shown below.
WP_STUB_FILE="~/.composer/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php" php -d memory_limit=512M ~/.composer/vendor/bin/php-scoper add-prefix

# After php-scoper has finished, we need to dump the autoloader from within the "build" directory.
composer dump --classmap-authoritative --working-dir=build

# Finally move the scoped "build" dir into your WordPress plugins dir and rename to "clockwork-for-wp".
mv build /srv/www/wordpress/public_html/wp-content/plugins/clockwork-for-wp
```
