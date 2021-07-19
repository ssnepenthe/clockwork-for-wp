<?php

/**
 * Plugin Name: CFW Except Preflight
 * Plugin URI: https://github.com/ssnepenthe/clockwork-for-wp
 * Description: An example plugin for enabling clockwork on OPTIONS requests.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

\add_action( 'cfw_config_init', function( $config ) {
	$config->set( 'requests.except_preflight', false );
} );
