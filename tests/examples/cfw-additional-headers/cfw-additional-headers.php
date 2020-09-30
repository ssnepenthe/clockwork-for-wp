<?php

/**
 * Plugin Name: CFW Additional Headers
 * Plugin URI: https://github.com/ssnepenthe/clockwork-for-wp
 * Description: An example plugin for adding additional clockwork headers.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

add_action( 'cfw_config_init', function( $config ) {
	$config
		->set_filtered_uris(
			array_merge( [ 'sample-page' ], $config->get_filtered_uris() )
		)
		->set_headers( [
			'Apples' => 'Bananas',
			'Cats' => 'Dogs',
		] );
} );
