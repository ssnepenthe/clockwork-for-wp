<?php

namespace Clockwork_For_Wp;

use Pimple\Container;
use Pimple\ServiceProviderInterface as Provider;

class WordPress_Provider implements Provider {
	/**
     * @param Container $pimple A container instance
     */
	public function register( Container $container ) {
		$container['timestart'] = $container->factory( function() {
			return $GLOBALS['timestart'];
		} );

		$container['wp'] = $container->factory( function() {
			return $GLOBALS['wp'];
		} );

		$container['wpdb'] = $container->factory( function() {
			return $GLOBALS['wpdb'];
		} );

		$container['wp_object_cache'] = $container->factory( function() {
			if ( ! isset( $GLOBALS['wp_object_cache'] ) && function_exists( 'wp_cache_init' ) ) {
				wp_cache_init();
			}

			return $GLOBALS['wp_object_cache'];
		} );

		$container['wp_rewrite'] = $container->factory( function() {
			return $GLOBALS['wp_rewrite'];
		} );

		$container['wp_query'] = $container->factory( function() {
			return $GLOBALS['wp_query'];
		} );
	}
}
