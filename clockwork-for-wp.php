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

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// @todo Verify server requirements are met.

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
		->on( 'plugins_loaded', [ $plugin, 'boot' ], Event_Manager::EARLY_EVENT );
} )( _cfw_instance() );
