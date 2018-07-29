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

// @todo Verify server requirements are met.

function _cfw_init() {
	static $initialized = false;

	if ( $initialized ) {
		return;
	}

	// @todo Move to external file to prevent namespaces killing execution on 5.2?
    $plugin = new Clockwork_For_Wp\Plugin();

    $plugin->register(new Clockwork_For_Wp\WordPress_Provider());
    $plugin->register( new Clockwork_For_Wp\Plugin_Provider() );

    add_action( 'plugins_loaded', [ $plugin, 'boot' ] );

	$initialized = true;
}

_cfw_init();
