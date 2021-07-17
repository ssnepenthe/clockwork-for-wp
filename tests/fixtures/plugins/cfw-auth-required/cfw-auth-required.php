<?php

/**
 * Plugin Name: CFW Auth Required
 * Plugin URI: https://github.com/ssnepenthe/clockwork-for-wp
 * Description: An example plugin for testing the clockwork auth mechanism.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

\add_action( 'cfw_config_init', function( $config ) {
	$config->set( 'authentication.enabled', true );
	$config->set( 'authentication.drivers.simple.config.password', 'nothing-to-see-here-folks' );
} );
