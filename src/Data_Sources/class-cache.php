<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Cache extends DataSource {
	protected $object_cache;
	protected $setted_transients = [];
	protected $deleted_transients = [];

	public function __construct( $object_cache ) {
		$this->set_object_cache( $object_cache );
	}

	public function on_setted_transient( $transient, $value, $expiration ) {
		$this->record_setted_transient( $transient, $value, $expiration, 'blog' );
	}

	public function on_setted_site_transient( $transient, $value, $expiration ) {
		$this->record_setted_transient( $transient, $value, $expiration, 'site' );
	}

	public function on_deleted_transient( $transient ) {
		$this->record_deleted_transient( $transient, 'blog' );
	}

	public function on_deleted_site_transient( $transient ) {
		$this->record_deleted_transient( $transient, 'site' );
	}

	public function resolve( Request $request ) {
		// "Caching" to avoid confusion with the included "Cache" tab.
		$panel = $request->userData( 'caching' )->title( 'Caching' );

		$panel->counters( $this->object_cache_counters() );

		if ( 0 !== count( $this->setted_transients ) ) {
			$panel->table( 'Setted Transients', $this->setted_transients );
		}

		if ( 0 !== count( $this->deleted_transients ) ) {
			$panel->table( 'Deleted Transients', $this->deleted_transients );
		}

		return $request;
	}

	public function set_object_cache( $object_cache ) {
		$this->object_cache = is_object( $object_cache ) ? $object_cache : null;
	}

	protected function object_cache_counters() {
		// @todo Would it be worth verifying props are public? (get_object_vars())
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

	protected function record_deleted_transient( $key, $type ) {
		$this->deleted_transients[] = [
			'Key' => $key,
			'Type' => $type,
		];
	}

	protected function record_setted_transient( $key, $value, $expiration, $type ) {
		$this->setted_transients[] = [
			'Key' => $key,
			'Value' => $value,
			'Expiration' => $expiration,
			'Type' => $type,
			'Size' => strlen( maybe_serialize( $value ) ),
		];
	}
}
