<?php

namespace Cfw_Test_Helper;

/**
 * Plugin Name: CFW Test Helper
 * Plugin URI: https://github.com/ssnepenthe/clockwork-for-wp
 * Description: A helper plugin for running the Clockwork for WP cypress test suite.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

function deactivate() {
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

	\deactivate_plugins( __FILE__ );
}

function notify( $message ) {
	echo '<div class="notice notice-error">';
	echo '<p>CFW Test Helper deactivated: ';
	echo \esc_html( $message );
	echo '</p>';
	echo '</div>';
}

if ( ! \function_exists( 'wp_get_environment_type' ) ) {
	\add_action( 'admin_init', __NAMESPACE__ . '\\deactivate' );
	\add_action( 'admin_notices', function() {
		notify( 'This plugin requires WordPress version 5.5.0 or greater' );
	} );
	return;
}

if ( 'production' === \wp_get_environment_type() ) {
	\add_action( 'admin_init', __NAMESPACE__ . '\\deactivate' );
	\add_action( 'admin_notices', function() {
		notify( 'This plugin can only run on non-production environments' );
	} );
	return;
}

if ( ! \function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! \is_plugin_active( 'clockwork-for-wp/clockwork-for-wp.php' ) ) {
	\add_action( 'admin_init', __NAMESPACE__ . '\\deactivate' );
	\add_action( 'admin_notices', function() {
		notify( 'This plugin requires Clockwork for WP to be installed and active' );
	} );
	return;
}

const CONFIG_KEY = 'cfwth_config';

require_once __DIR__ . '/actual-plugin-stuff.php';
require_once __DIR__ . '/ajax-handlers.php';
require_once __DIR__ . '/class-config-fetcher.php';
require_once __DIR__ . '/class-metadata.php';
require_once __DIR__ . '/obnoxious-stuff.php';

(function() {
	$namespace = __NAMESPACE__;
	$ajax_actions = [
		'request_by_id',
		'clean_requests',
		'create_requests',
		'set_config',
		'reset_config',
	];

	// @todo wp_body_open may not be sufficent depending on currently active theme.
	\add_action( 'wp_body_open', "{$namespace}\\obnoxious_frontend_warning" );
	\add_action( 'admin_notices', "{$namespace}\\obnoxious_admin_warning" );

	\add_action( 'cfw_config_init', "{$namespace}\\apply_config" );
	\add_action( 'wp_footer', "{$namespace}\\print_test_context" );

	foreach ( $ajax_actions as $action ) {
		\add_action( "wp_ajax_cfwth_{$action}", "{$namespace}\\{$action}" );
		\add_action( "wp_ajax_nopriv_cfwth_{$action}", "{$namespace}\\{$action}" );
	}
})();
