# Clockwork for WP
A basic [Clockwork](https://underground.works/clockwork/) integration for WordPress.

**DO NOT, UNDER ANY CIRCUMSTANCES, RUN THIS PLUGIN ON ANYTHING BUT A DEVELOPMENT MACHINE - THE METADATA IT EXPOSES CAN BE USED TO EASILY TAKE OVER YOUR SITE.**

## Requirements
PHP 7.1 or greater, WP 5.5 or greater and Composer.

Optionally install one of the Clockwork browser extensions ([Chrome](https://chrome.google.com/webstore/detail/clockwork/dmggabnehkmmfmdffgajcflpdjlnoemp), [Firefox](https://addons.mozilla.org/en-US/firefox/addon/clockwork-dev-tools/)).

## Installation
For now either [require dev-master via Composer using a vcs repository](https://getcomposer.org/doc/05-repositories.md#vcs) or clone this repo into your plugins directory and manually run `composer install`.

This plugin is not meant to run on production - make sure you have configured your [environment type](https://developer.wordpress.org/reference/functions/wp_get_environment_type/) to something other than 'production'.

## Usage
Once the plugin has been activated, there are two primary options for usage:

If you have installed the browser extension, open developer tools and browse to the Clockwork tab.

Otherwise, open a new browser tab and navigate to the `__clockwork/app` endpoint (e.g. https://example.com/__clockwork/app).

If you are trying to debug outside of an HTML context (e.g. wp-cron, rest api, admin-ajax, wp-cli), you must to use the webapp instead of the browser extension.

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

If you would like to collect WP-CLI output, it can be beneficial to add the "initialize-wp-cli-logger.php" file to the list of requires in your [WP-CLI config](https://make.wordpress.org/cli/handbook/references/config/) to ensure we are capturing as much output as possible.
