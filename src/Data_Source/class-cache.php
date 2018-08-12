<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Cache extends DataSource {
	protected $object_cache;
	protected $transients;

	public function __construct( $object_cache ) {
		$this->set_object_cache( $object_cache );
		$this->transients = [
			'deleted' => [],
			'setted' => [],
		];
	}

	public function resolve( Request $request ) {
		// "Caching" to avoid confusion with the included "Cache" tab.
		$panel = $request->userData( 'caching' )->title( 'Caching' );

		$panel->counters( $this->object_cache_counters() );

		if ( 0 !== count( $this->transients['setted'] ) ) {
			$panel->table( 'Setted Transients', $this->transients['setted'] );
		}

		if ( 0 !== count( $this->transients['deleted'] ) ) {
			$panel->table( 'Deleted Transients', $this->transients['deleted'] );
		}

		return $request;
	}

	public function listen_to_events() {
		add_action( 'setted_transient', function( $transient, $value, $expiration ) {
			$this->set_transient( $transient, $value, $expiration, 'blog' );
		}, 10, 3 );

		add_action( 'setted_site_transient', function( $transient, $value, $expiration ) {
			$this->set_transient( $transient, $value, $expiration, 'site' );
		}, 10, 3 );

		add_action( 'deleted_transient', function( $transient ) {
			$this->delete_transient( $transient, 'blog' );
		} );

		add_action( 'deleted_site_transient', function( $transient ) {
			$this->delete_transient( $transient, 'site' );
		} );
	}

	public function set_object_cache( $object_cache ) {
		$this->object_cache = is_object( $object_cache ) ? $object_cache : null;
	}

	protected function delete_transient( $key, $type ) {
		$this->transients['deleted'][] = [
			'Key' => $key,
			'Type' => $type,
		];
	}

	protected function object_cache_counters() {
		// @todo Would it be worth verifying props are public? I think this requires reflection.
		// @todo Include hit percentage?
		$hits = $misses = $writes = $deletes = 0;

		if ( null !== $this->object_cache ) {
			if ( property_exists( $this->object_cache, 'cache_hits' ) ) {
				$hits += (int) $this->object_cache->cache_hits;
			}

			if ( property_exists( $this->object_cache, 'cache_misses' ) ) {
				$misses += (int) $this->object_cache->cache_misses;
			}

			if ( property_exists( $this->object_cache, 'redis_calls' ) ) {
				foreach ( [ 'hIncrBy', 'decrBy', 'incrBy', 'hSet', 'set', 'setex' ] as $method ) {
					if ( isset( $this->object_cache->redis_calls[ $method ] ) ) {
						$writes += (int) $this->object_cache->redis_calls[ $method ];
					}
				}

				foreach ( [ 'hDel', 'del', 'flushAll' ] as $method ) {
					if ( isset( $this->object_cache->redis_calls[ $method ] ) ) {
						$deletes += (int) $this->object_cache->redis_calls[ $method ];
					}
				}
			} elseif ( property_exists( $this->object_cache, 'stats' ) ) {
				if ( isset( $this->object_cache->stats['add'] ) ) {
					$writes += (int) $this->object_cache->stats['add'];
				}

				if ( isset( $this->object_cache->stats['deletes'] ) ) {
					$deletes += (int) $this->object_cache->stats['deletes'];
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

	protected function set_transient( $key, $value, $expiration, $type ) {
		$this->transients['setted'][] = [
			'Key' => $key,
			'Value' => $value,
			'Expiration' => $expiration,
			'Type' => $type,
			'Size' => strlen( maybe_serialize( $value ) ),
		];
	}
}
