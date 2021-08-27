<?php
/**
 * If you would like to collect WP-CLI output, it can be beneficial to add this file to the list of
 * "requires" in your WP-CLI config file.
 *
 * Commands defined within plugins are loaded after most of the WP-CLI bootstrap process has been
 * completed which means we miss any output generated by WP-CLI itself.
 *
 * Requiring this file in your WP-CLI config ensures that our logger is initialized as early as
 * possible so we can capture as much output as possible.
 *
 * @see https://make.wordpress.org/cli/handbook/references/config/
 */

use Clockwork_For_Wp\Wp_Cli\Cli_Collection_Helper;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

Cli_Collection_Helper::initialize_logger();