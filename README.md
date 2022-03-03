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
For now either [require dev-master via Composer using a vcs repository](https://getcomposer.org/doc/05-repositories.md#vcs) or clone this repo into your plugins directory and manually run `composer install`.

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

Your callback will receive an instance of `\Clockwork_For_Wp\Private_Schema_Configuration` which implements both `\League\Config\ConfigurationInterface` and `\League\Config\MutableConfigurationInterface`.

This config instance can be used to change any of the configuration options found in `config/schema.php`.

Note that while most config defaults are set directly in the schema, some are set separately in `config/defaults.php` - please check both files.

For example, consider the following must-use plugin at `wp-content/mu-plugins/cfw-config.php`:

```php
\add_action( 'cfw_config_init', function( $config ) {
    // Modify existing options using the get and set method:

    // Enable all data sources at once:
    $data_sources = array_map( function( $data_source ) {
        $data_source['enabled'] = true;

        return $data_source;
    }, $config->get( 'data_sources' ) );

    $config->set( 'data_sources', $data_sources );

    // OR set individual options using the set method with dot notation:

    // Disables the Clockwork webapp.
    $config->set( 'web', false );

    // Sets the metadata expiration to one day (in minutes).
    $config->set( 'storage.expiration', 60 * 24 );

    // OR set multiple options at once using the merge method:

    $config->merge( [
        'requests' => [
            // Enables collection of data on OPTIONS requests.
            'except_preflight' => false,
        ],
        'wp_cli' => [
            // Enables collection of data when running WP-CLI.
            'collect' => true,
        ],
    ] );
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
