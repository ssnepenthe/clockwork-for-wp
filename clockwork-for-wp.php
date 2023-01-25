<?php

declare(strict_types=1);

use Clockwork_For_Wp\Plugin;

/*
 * A basic Clockwork integration for WordPress.
 *
 * @package clockwork_for_wp
 */

/*
 * Plugin Name: Clockwork for WP
 * Plugin URI: https://github.com/ssnepenthe/clockwork-for-wp
 * Description: A basic <a href="https://underground.works/clockwork/">Clockwork</a> integration for WordPress.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

function _cfw_deactivate_self(): void {
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

	\deactivate_plugins( __FILE__ );
}

function _cfw_admin_error_notice( $message ): void {
	$notice = <<<'EOD'
<div class="notice notice-error">
	<p>Clockwork for WP: %s</p>
</div>
EOD;

	\printf( $notice, \esc_html( $message ) );
}

if ( ! \function_exists( 'wp_get_environment_type' ) ) {
	\add_action( 'admin_init', '_cfw_deactivate_self' );
	\add_action(
		'admin_notices',
		static function (): void {
			\_cfw_admin_error_notice( 'Plugin deactivated: This plugin requires WordPress version 5.5.0 or greater.' );
		}
	);

	return;
}

if (
	'production' === \wp_get_environment_type()
	&& ! ( \defined( 'CFW_RUN_ON_PROD' ) && CFW_RUN_ON_PROD )
) {
	\add_action( 'admin_init', '_cfw_deactivate_self' );
	\add_action(
		'admin_notices',
		static function (): void {
			\_cfw_admin_error_notice( 'Plugin deactivated: This plugin can only run on non-production environments.' );
		}
	);

	return;
}

if ( ! \file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	\add_action(
		'admin_notices',
		static function (): void {
			\_cfw_admin_error_notice( 'Plugin not loaded: Unable to locate Composer autoloader.' );
		}
	);

	return;
}

require_once __DIR__ . '/vendor/autoload.php';

// @todo Check for minimum php version.
// @todo Check that dependencies have been installed.

require_once __DIR__ . '/src/instance.php';

\_cfw_instance()->run();
