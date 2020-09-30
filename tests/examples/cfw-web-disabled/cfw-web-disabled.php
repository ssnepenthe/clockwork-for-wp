<?php

/**
 * Plugin Name: CFW Web Disabled
 * Plugin URI: https://github.com/ssnepenthe/clockwork-for-wp
 * Description: An example plugin for disabling the clockwork web app.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

add_action( 'cfw_config_init', function( $config ) {
	$config->set_web_enabled( false );
} );
