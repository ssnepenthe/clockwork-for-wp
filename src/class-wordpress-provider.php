<?php

namespace Clockwork_For_Wp;

use Pimple\Container;
use Pimple\ServiceProviderInterface as Provider;

class WordPress_Provider implements Provider {
	/**
     * @param Container $pimple A container instance
     */
	public function register( Container $container ) {
		// Typically set by theme on 'after_setup_theme'.
		$container['content_width'] = $container->factory( function() {
			return isset( $GLOBALS['content_width'] ) ? $GLOBALS['content_width'] : null;
		} );

		// Available long before plugins ever load.
		$container['timestart'] = $container->factory( function() {
			return isset( $GLOBALS['timestart'] ) ? $GLOBALS['timestart'] : null;
		} );

		// Available after 'plugins_loaded' - between 'sanitize_comment_cookies' and 'setup_theme'.
		$container['wp'] = $container->factory( function() {
			return isset( $GLOBALS['wp'] ) ? $GLOBALS['wp'] : null;
		} );

		// Available long before plugins ever load.
		$container['wpdb'] = $container->factory( function() {
			return isset( $GLOBALS['wpdb'] ) ? $GLOBALS['wpdb'] : null;
		} );

		// Available long before plugins ever load, just after wpdb is initialized.
		$container['wp_object_cache'] = $container->factory( function() {
			if ( ! isset( $GLOBALS['wp_object_cache'] ) && function_exists( 'wp_cache_init' ) ) {
				wp_cache_init();
			}

			return isset( $GLOBALS['wp_object_cache'] ) ? $GLOBALS['wp_object_cache'] : null;
		} );

		// First initialized on 'parse_request' via 'rest_api_loaded()'.
		$container['wp_rest_server'] = $container->factory( function() {
			return rest_get_server();
		} );

		// Available after 'plugins_loaded' - between 'sanitize_comment_cookies' and 'setup_theme'.
		$container['wp_rewrite'] = $container->factory( function() {
			return isset( $GLOBALS['wp_rewrite'] ) ? $GLOBALS['wp_rewrite'] : null;
		} );

		// Available after 'plugins_loaded' - between 'sanitize_comment_cookies' and 'setup_theme'.
		$container['wp_query'] = $container->factory( function() {
			return isset( $GLOBALS['wp_query'] ) ? $GLOBALS['wp_query'] : null;
		} );
	}
}
