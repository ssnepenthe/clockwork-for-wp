# Clockwork for WP
A basic [Clockwork](https://underground.works/clockwork/) integration for WordPress.

**DO NOT, UNDER ANY CIRCUMSTANCES, RUN THIS PLUGIN ON ANYTHING BUT A DEVELOPMENT MACHINE - THE METADATA IT EXPOSES CAN BE USED TO EASILY TAKE OVER YOUR SITE.**

## Requirements
PHP 5.5 or greater, WP 5.5 or greater and Composer.

Optionally install one of the Clockwork browser extensions ([Chrome](https://chrome.google.com/webstore/detail/clockwork/dmggabnehkmmfmdffgajcflpdjlnoemp), [Firefox](https://addons.mozilla.org/en-US/firefox/addon/clockwork-dev-tools/)).

## Installation
For now either [require dev-master via Composer using a vcs repository](https://getcomposer.org/doc/05-repositories.md#vcs) or clone this repo into your plugins directory and manually run `composer install`.

This plugin is not meant to run on production - make sure you have configured your [environment type](https://developer.wordpress.org/reference/functions/wp_get_environment_type/) to something other than 'production'.

## Usage
Once the plugin has been activated, there are two primary options for usage:

If you have installed the browser extension, open developer tools and browse to the Clockwork tab.

Otherwise, open a new browser tab and navigate to the `__clockwork/app` endpoint (e.g. https://mysite.com/__clockwork/app).

## Configuration
Clockwork can be configured using the `cfw_config_init` action from within a must-use plugin.

Your callback should accept an instance of `\Clockwork_For_Wp\Config` which can be used to change any of the configuration options found in `src/config.php`. Options are set using dot notation.

For example, consider the following must-use plugin at `wp-content/mu-plugins/cfw-config.php`:

```php
\add_action( 'cfw_config_init', function( \Clockwork_For_Wp\Config $config ) {
    // Disables the Clockwork webapp.
    $config->set( 'web', false );

    // Disables the WP_Rewrite data source.
    $config->set( 'data_sources.wp_rewrite.enabled', false );

    // Sets the expiration for file-based metadata storage to one day (in minutes).
    $config->set( 'storage.drivers.file.config.expiration', 60 * 24 );
} );
```
