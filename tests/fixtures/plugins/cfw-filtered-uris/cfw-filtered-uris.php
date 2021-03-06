<?php

/**
 * Plugin Name: CFW Filtered URIs
 * Plugin URI: https://github.com/ssnepenthe/clockwork-for-wp
 * Description: An example plugin for disabling clockwork by URL pattern.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

\add_action( 'cfw_config_init', function( $config ) {
	$config->set(
		'filter_uris',
		\array_merge( [ 'sample-page' ], $config->get( 'filter_uris', [] ) )
	);
} );
