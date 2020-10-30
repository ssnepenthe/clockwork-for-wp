<?php

/**
 * Plugin Name: CFW Collect Data Always
 * Plugin URI: https://github.com/ssnepenthe/clockwork-for-wp
 * Description: An example plugin for ensuring clockwork is always collecting data.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

\add_action( 'cfw_config_init', function( $config ) {
	$config->set( 'enable', false );
	$config->set( 'collect_data_always', true );
} );

// Clockwork headers won't be sent since we have "disabled" clockwork...
// Let's output some debug info in the footer.
\add_action( 'wp_footer', function() {
	\printf(
		'<span class="testing-clockwork-id">%s</span>',
		\_cfw_instance()[ Clockwork\Clockwork::class ]->getRequest()->id
	);
} );
