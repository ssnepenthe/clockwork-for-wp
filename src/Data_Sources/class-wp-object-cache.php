<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp_Object_Cache extends DataSource {
	protected $wp_object_cache;

	public function __construct( $wp_object_cache = null ) {
		$this->set_wp_object_cache( $wp_object_cache );
	}

	public function get_wp_object_cache() {
		return $this->wp_object_cache;
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'Caching' );

		$panel->counters( $this->counts_list() );

		return $request;
	}

	public function set_wp_object_cache( $wp_object_cache ) {
		$this->wp_object_cache = is_object( $wp_object_cache ) ? $wp_object_cache : null;
	}

	protected function counts_list() {
		// @todo Would it be worth verifying props are public? (get_object_vars())
		// @todo Include hit percentage?
		$hits = $misses = $writes = $deletes = 0;

		if ( null !== $this->wp_object_cache ) {
			if ( property_exists( $this->wp_object_cache, 'cache_hits' ) ) {
				$hits += (int) $this->wp_object_cache->cache_hits;
			}

			if ( property_exists( $this->wp_object_cache, 'cache_misses' ) ) {
				$misses += (int) $this->wp_object_cache->cache_misses;
			}

			if ( property_exists( $this->wp_object_cache, 'redis_calls' ) ) {
				foreach ( [ 'hIncrBy', 'decrBy', 'incrBy', 'hSet', 'set', 'setex' ] as $method ) {
					if ( isset( $this->wp_object_cache->redis_calls[ $method ] ) ) {
						$writes += (int) $this->wp_object_cache->redis_calls[ $method ];
					}
				}

				foreach ( [ 'hDel', 'del', 'flushAll' ] as $method ) {
					if ( isset( $this->wp_object_cache->redis_calls[ $method ] ) ) {
						$deletes += (int) $this->wp_object_cache->redis_calls[ $method ];
					}
				}
			} elseif ( property_exists( $this->wp_object_cache, 'stats' ) ) {
				if ( isset( $this->wp_object_cache->stats['add'] ) ) {
					$writes += (int) $this->wp_object_cache->stats['add'];
				}

				if ( isset( $this->wp_object_cache->stats['deletes'] ) ) {
					$deletes += (int) $this->wp_object_cache->stats['deletes'];
				}
			}
		}

		return array_filter( [
			'Reads' => $hits + $misses,
			'Hits' => $hits,
			'Misses' => $misses,
			'Writes' => $writes,
			'Deletes' => $deletes,
		] );
	}
}
