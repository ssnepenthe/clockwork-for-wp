<?php

namespace Clockwork_For_Wp;

use WP_Object_Cache;
use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp_Object_Cache_Data_Source extends DataSource {
	/**
	 * @var WP_Object_Cache
	 */
	protected $cache;

	// @todo Maybe drop the type-hint? Are there any circumstances where this wouldn't be available?
	public function __construct( WP_Object_Cache $cache ) {
		$this->cache = $cache;
	}

	public function resolve( Request $request ) {
		$request = $this->collect_data( $request );

		return $request;
	}

	protected function collect_data( $request ) {
		if ( ! is_object( $this->cache ) ) {
			return $request;
		}

		// WP-Redis dropin by Pantheon Systems.
		if ( property_exists( $this->cache, 'redis' ) ) {
			return $this->collect_wp_redis_data( $request );
		}

		// Memcached dropin by Ryan Boren - I suspect these stats aren't very accurate.
		if ( property_exists( $this->cache, 'mc' ) ) {
			return $this->collect_memcached_data( $request );
		}

		return $this->collect_core_data( $request );
	}

	protected function collect_wp_redis_data( $request ) {
		$hits = 0;
		$misses = 0;
		$writes = 0;
		$deletes = 0;

		// @todo Maybe we should re-use this logic from 'collect_core_data()'?
		if ( property_exists( $this->cache, 'cache_hits' ) ) {
			$hits = (int) $this->cache->cache_hits;
		}

		// @todo Maybe we should re-use this logic from 'collect_core_data()'?
		if ( property_exists( $this->cache, 'cache_misses' ) ) {
			$misses = (int) $this->cache->cache_misses;
		}

		if ( property_exists( $this->cache, 'redis_calls' ) ) {
			foreach ( [ 'hIncrBy', 'decrBy', 'incrBy', 'hSet', 'set', 'setex' ] as $method ) {
				if ( isset( $this->cache->redis_calls[ $method ] ) ) {
					$writes += (int) $this->cache->redis_calls[ $method ];
				}
			}

			foreach ( [ 'hDel', 'del', 'flushAll' ] as $method ) {
				if ( isset( $this->cache->redis_calls[ $method ] ) ) {
					$deletes += (int) $this->cache->redis_calls[ $method ];
				}
			}
		}

		// @todo WP-Redis also tracks 'hExists' and 'exists' calls...

		$reads = $hits + $misses;

		if ( $hits ) {
			$request->cacheHits = $hits;
		}

		if ( $reads ) {
			$request->cacheReads = $reads;
		}

		if ( $writes ) {
			$request->cacheWrites = $writes;
		}

		if ( $deletes ) {
			$request->cacheDeletes = $deletes;
		}

		return $request;
	}

	protected function collect_memcached_data( $request ) {
		if ( ! property_exists( $this->cache, 'stats' ) ) {
			return $request;
		}

		$reads = 0;
		$writes = 0;
		$deletes = 0;

		if ( isset( $this->cache->stats['get'] ) ) {
			$reads += (int) $this->cache->stats['get'];
		}

		if ( isset( $this->cache->stats['get_multi'] ) ) {
			$reads += (int) $this->cache->stats['get_multi'];
		}

		if ( isset( $this->cache->stats['add'] ) ) {
			$writes += (int) $this->cache->stats['add'];
		}

		if ( isset( $this->cache->stats['delete'] ) ) {
			$deletes += (int) $this->cache->stats['delete'];
		}

		// @todo This dropin maps 'get' ops to 'cache_hits' prop and 'add' ops to 'cache_misses' - should we be using them directly instead? Would allow for a certain amount of shared logic between all dropins.
		if ( $reads ) {
			$request->cacheReads = $reads;
		}

		if ( $writes ) {
			$request->cacheWrites = $writes;
		}

		if ( $deletes ) {
			$request->cacheDeletes = $deletes;
		}

		return $request;
	}

	protected function collect_core_data( $request ) {
		$hits = 0;
		$misses = 0;

		if ( property_exists( $this->cache, 'cache_hits' ) ) {
			$hits = (int) $this->cache->cache_hits;
		}

		if ( property_exists( $this->cache, 'cache_misses' ) ) {
			$misses = $this->cache->cache_misses;
		}

		$reads = $hits + $misses;

		if ( $hits ) {
			$request->cacheHits = $hits;
		}

		if ( $reads ) {
			$request->cacheReads = $reads;
		}

		return $request;
	}
}
