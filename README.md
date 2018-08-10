# Clockwork for WP
A basic [Clockwork](https://underground.works/clockwork/) integration for WordPress.

## Requirements
PHP 5.5 or greater and Composer.

Optionally install one of the Clockwork browser extensions ([Chrome](https://chrome.google.com/webstore/detail/clockwork/dmggabnehkmmfmdffgajcflpdjlnoemp), [Firefox](https://addons.mozilla.org/en-US/firefox/addon/clockwork-dev-tools/)).

## Installation
For now either [require dev-master via Composer using a vcs repository](https://getcomposer.org/doc/05-repositories.md#vcs) or clone this repo into your plugins directory and manually run `composer install`.

## Usage
Once the plugin has been activated, there are two primary options for usage:

If you have installed the browser extension, open developer tools and browse to the Clockwork tab.

Otherwise, open a new browser tab and navigate to the `__clockwork/app` endpoint (e.g. https://mysite.com/__clockwork/app).

## Configuration
There are three options for configuring Clockwork:

`cfw_config_args` filter - This filter allows you to modify the args array before the config object is constructed.

`cfw_config_init` action - This action provides access to the config object after it has been constructed. You can modify all available options using their respective setter methods.

The [Pimple `extend` method](https://pimple.symfony.com/#modifying-services-after-definition) - The core of this plugin is an instance of Pimple which can be retrieved using the `_cfw_instance` function. The config definition is stored using the `'config'` identifier.
