<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use WP;
use WP_Object_Cache;
use WP_Query;
use WP_REST_Server;
use WP_Rewrite;
use wpdb;

/**
 * @internal
 */
final class Wordpress_Provider extends Base_Provider {
	public function register(): void {
		require_once __DIR__ . '/wordpress-helpers.php';

		$pimple = $this->plugin->get_pimple();

		// @todo consider prefixing params that are not type-hintable to avoid accidental injections.
		$pimple['content_width'] = $pimple->factory( static function () {
			return $GLOBALS['content_width'] ?? 0;
		} );

		$pimple['timestart'] = $pimple->factory( static function () {
			return $GLOBALS['timestart'];
		} );

		$pimple['wp_actions'] = $pimple->factory( static function () {
			return $GLOBALS['wp_actions'];
		} );

		$pimple['wp_filter'] = $pimple->factory( static function () {
			return $GLOBALS['wp_filter'];
		} );

		$pimple['wp_version'] = $pimple->factory( static function () {
			return $GLOBALS['wp_version'];
		} );

		$pimple[ WP::class ] = $pimple->factory( static function () {
			return $GLOBALS['wp'];
		} );

		$pimple[ WP_Object_Cache::class ] = $pimple->factory( static function () {
			if ( ! isset( $GLOBALS['wp_object_cache'] ) && \function_exists( 'wp_cache_init' ) ) {
				\wp_cache_init();
			}

			return $GLOBALS['wp_object_cache'];
		} );

		$pimple[ WP_Query::class ] = $pimple->factory( static function () {
			return $GLOBALS['wp_query'];
		} );

		$pimple[ WP_REST_Server::class ] = $pimple->factory( static function () {
			return \rest_get_server();
		} );

		$pimple[ WP_Rewrite::class ] = $pimple->factory( static function () {
			return $GLOBALS['wp_rewrite'];
		} );

		$pimple[ wpdb::class ] = $pimple->factory( static function () {
			return $GLOBALS['wpdb'];
		} );
	}
}
