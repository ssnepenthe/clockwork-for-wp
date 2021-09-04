<?php

namespace Clockwork_For_Wp;

use WP;
use WP_Object_Cache;
use WP_Query;
use WP_REST_Server;
use WP_Rewrite;
use wpdb;

class Wordpress_Provider extends Base_Provider {
	public function register() {
		require_once $this->plugin['dir'] . '/src/wordpress-helpers.php';

		// @todo consider prefixing params that are not type-hintable to avoid accidental injections.
		$this->plugin['content_width'] = $this->plugin->factory( function () {
			return $GLOBALS['content_width'];
		} );

		$this->plugin['timestart'] = $this->plugin->factory( function () {
			return $GLOBALS['timestart'];
		} );

		$this->plugin['wp_actions'] = $this->plugin->factory( function () {
			return $GLOBALS['wp_actions'];
		} );

		$this->plugin['wp_filter'] = $this->plugin->factory( function () {
			return $GLOBALS['wp_filter'];
		} );

		$this->plugin['wp_version'] = $this->plugin->factory( function () {
			return $GLOBALS['wp_version'];
		} );

		$this->plugin[ WP::class ] = $this->plugin->factory( function () {
			return $GLOBALS['wp'];
		} );

		$this->plugin[ WP_Object_Cache::class ] = $this->plugin->factory( function () {
			if ( ! isset( $GLOBALS['wp_object_cache'] ) && function_exists( 'wp_cache_init' ) ) {
				wp_cache_init();
			}

			return $GLOBALS['wp_object_cache'];
		} );

		$this->plugin[ WP_Query::class ] = $this->plugin->factory( function () {
			return $GLOBALS['wp_query'];
		} );

		$this->plugin[ WP_REST_Server::class ] = $this->plugin->factory( function () {
			return rest_get_server();
		} );

		$this->plugin[ WP_Rewrite::class ] = $this->plugin->factory( function () {
			return $GLOBALS['wp_rewrite'];
		} );

		$this->plugin[ wpdb::class ] = $this->plugin->factory( function () {
			return $GLOBALS['wpdb'];
		} );
	}
}
