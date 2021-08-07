<?php

use Clockwork_For_Wp\Data_Source\Errors;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Plugin;
/**
 * A basic Clockwork integration for WordPress.
 *
 * @package clockwork_for_wp
 */

/**
 * Plugin Name: Clockwork for WP
 * Plugin URI: https://github.com/ssnepenthe/clockwork-for-wp
 * Description: A basic <a href="https://underground.works/clockwork/">Clockwork</a> integration for WordPress.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function _cfw_deactivate_self() {
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

	deactivate_plugins( __FILE__ );
}

function _cfw_admin_error_notice( $message ) {
	$notice = <<<EOD
<div class="notice notice-error">
	<p>Clockwork for WP deactivated: %s</p>
</div>
EOD;

	printf( $notice, esc_html( $message ) );
}

if ( ! function_exists( 'wp_get_environment_type' ) ) {
	add_action( 'admin_init', '_cfw_deactivate_self' );
	add_action( 'admin_notices', function() {
		_cfw_admin_error_notice( 'This plugin requires WordPress version 5.5.0 or greater.' );
	} );

	return;
}

if (
	'production' === wp_get_environment_type()
	&& ! ( defined( 'CFW_RUN_ON_PROD' ) && CFW_RUN_ON_PROD )
) {
	add_action( 'admin_init', '_cfw_deactivate_self' );
	add_action( 'admin_notices', function() {
		_cfw_admin_error_notice( 'This plugin can only run on non-production environments.' );
	} );

	return;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// @todo Check for minimum php version.
// @todo Check that dependencies have been installed.

require_once __DIR__ . '/src/helpers.php';

function _cfw_instance() {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new Plugin();
	}

	return $instance;
}

( function( $plugin ) {
	// Resolve error handler immediately so we catch as many errors as possible.
	// @todo Check config to make sure error feature is enabled? Or probably a constant?
	// @todo Move to plugin constructor?
	$plugin[ Errors::class ]->register();

	$plugin[ Event_Manager::class ]
		->on( 'plugin_loaded', function( $file ) use ( $plugin ) {
			if ( __FILE__ !== $file ) {
				return;
			}

			$plugin->lock();
		}, Event_Manager::EARLY_EVENT )
		->on( 'plugins_loaded', [ $plugin, 'boot' ], Event_Manager::EARLY_EVENT );
} )( _cfw_instance() );
