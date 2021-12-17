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

// @todo be obnoxiously intrusive about notifying the user that this plugin is active.

 function deactivate() {
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

	\deactivate_plugins( __FILE__ );
}

function notify( $message ) {
	echo '<div class="notice notice-error">';
	echo '<p>CFW Configure Over HTTP deactivated: ';
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

require_once __DIR__ . '/class-config-fetcher.php';
require_once __DIR__ . '/class-metadata.php';
require_once __DIR__ . '/ajax-handlers.php';

function apply_config( $config ) {
	$request_config = get_option( 'cfw_coh_config', null );

	if ( ! is_array( $request_config ) ) {
		$request_config = $_GET;
	}

	foreach ( ( new Config_Fetcher( $request_config ) )->get_config() as $key => $value ) {
		$config->set( $key, $value );
	}

	$requests_except = $config->get( 'requests.except', [] );
	$requests_except[] = 'action=cfwth_';

	$config->set( 'requests.except', $requests_except );
};

function print_test_context() {
	$context = [
		'ajaxUrl' => \admin_url( 'admin-ajax.php', 'relative' ),
		'clockworkVersion' => \Clockwork\Clockwork::VERSION,
	];

	printf(
		'<span data-cy="test-context">%s</span><span data-cy="request-id">%s</span>',
		json_encode( $context ),
		\esc_html( \_cfw_instance()[ \Clockwork\Request\Request::class ]->id )
	);
};

\add_action( 'cfw_config_init', __NAMESPACE__ . '\\apply_config' );
\add_action( 'wp_footer', __NAMESPACE__ . '\\print_test_context' );

$namespace = __NAMESPACE__;
$actions = [
	'request_by_id',
	'clean_requests',
	'create_requests',
	'set_config',
	'reset_config',
];

foreach ( $actions as $action ) {
	\add_action( "wp_ajax_cfwth_{$action}", "{$namespace}\\{$action}" );
	\add_action( "wp_ajax_nopriv_cfwth_{$action}", "{$namespace}\\{$action}" );
}
