<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Wp_Object_Cache;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Globals;

/**
 * @internal
 */
final class Wp_Object_Cache_Subscriber implements Subscriber {
	private Wp_Object_Cache $data_source;

	public function __construct( Wp_Object_Cache $data_source ) {
		$this->data_source = $data_source;
	}

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => 'on_cfw_pre_resolve',
		];
	}

	public function on_cfw_pre_resolve(): void {
		$wp_object_cache = Globals::get( 'wp_object_cache' );

		// @todo Include hit percentage?
		if ( \property_exists( $wp_object_cache, 'cache_hits' ) ) {
			$this->data_source->hit( (int) $wp_object_cache->cache_hits );
		}

		if ( \property_exists( $wp_object_cache, 'cache_misses' ) ) {
			$this->data_source->miss( (int) $wp_object_cache->cache_misses );
		}

		if ( \property_exists( $wp_object_cache, 'redis_calls' ) ) {
			foreach ( [ 'hIncrBy', 'decrBy', 'incrBy', 'hSet', 'set', 'setex' ] as $method ) {
				if ( isset( $wp_object_cache->redis_calls[ $method ] ) ) {
					$this->data_source->write( (int) $wp_object_cache->redis_calls[ $method ] );
				}
			}

			foreach ( [ 'hDel', 'del', 'flushAll' ] as $method ) {
				if ( isset( $wp_object_cache->redis_calls[ $method ] ) ) {
					$this->data_source->delete( (int) $wp_object_cache->redis_calls[ $method ] );
				}
			}
		} elseif ( \property_exists( $wp_object_cache, 'stats' ) ) {
			if ( isset( $wp_object_cache->stats['add'] ) ) {
				$this->data_source->write( (int) $wp_object_cache->stats['add'] );
			}

			if ( isset( $wp_object_cache->stats['deletes'] ) ) {
				$this->data_source->delete( (int) $wp_object_cache->stats['deletes'] );
			}
		}
	}
}
