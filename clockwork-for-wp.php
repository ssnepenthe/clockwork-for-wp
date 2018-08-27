<?php
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

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

require_once dirname( __FILE__ ) . '/inc/helpers.php';

// @todo Verify server requirements are met.

function _cfw_instance( $id = null ) {
	static $instance = null;

	if ( null === $instance ) {
		// @todo Move to external file to prevent namespaces killing execution on 5.2?
		$instance = new Clockwork_For_Wp\Plugin( [
			'dir' => dirname( __FILE__ ),
		] );

		// Resolve the error handler early to ensure we catch as many errors as possible.
		$instance->service( 'data_sources.errors' );
	}

	return $instance;
}

add_action( 'plugins_loaded', [ _cfw_instance(), 'boot' ] );
