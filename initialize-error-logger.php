<?php

declare(strict_types=1);

use Clockwork_For_Wp\Data_Source\Errors;

/*
 * By default the errors data source will log all errors that occur after the plugin has loaded as
 * well as the last error that occurred before the plugin loaded.
 *
 * If you want to capture earlier errors, you can manually register the clockwork error handler by
 * requiring this file early on in the WordPress bootstrap (e.g. from a must-use plugin).
 */

if ( \file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

Errors::get_instance()->register();
