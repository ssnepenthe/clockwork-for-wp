<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

class Wp_Object_Cache extends DataSource implements Subscriber {
	protected $hits = 0;
	protected $misses = 0;
	protected $writes = 0;
	protected $deletes = 0;

	public function subscribe_to_events( Event_Manager $event_manager ) : void {
		$event_manager->on( 'cfw_pre_resolve', function( \WP_Object_Cache $wp_object_cache ) {
			// @todo Include hit percentage?
			if ( property_exists( $wp_object_cache, 'cache_hits' ) ) {
				$this->hit( (int) $wp_object_cache->cache_hits );
			}

			if ( property_exists( $wp_object_cache, 'cache_misses' ) ) {
				$this->miss( (int) $wp_object_cache->cache_misses );
			}

			if ( property_exists( $wp_object_cache, 'redis_calls' ) ) {
				foreach ( [ 'hIncrBy', 'decrBy', 'incrBy', 'hSet', 'set', 'setex' ] as $method ) {
					if ( isset( $wp_object_cache->redis_calls[ $method ] ) ) {
						$this->write( (int) $wp_object_cache->redis_calls[ $method ] );
					}
				}

				foreach ( [ 'hDel', 'del', 'flushAll' ] as $method ) {
					if ( isset( $wp_object_cache->redis_calls[ $method ] ) ) {
						$this->delete( (int) $wp_object_cache->redis_calls[ $method ] );
					}
				}
			} elseif ( property_exists( $wp_object_cache, 'stats' ) ) {
				if ( isset( $wp_object_cache->stats['add'] ) ) {
					$this->write( (int) $wp_object_cache->stats['add'] );
				}

				if ( isset( $wp_object_cache->stats['deletes'] ) ) {
					$this->delete( (int) $wp_object_cache->stats['deletes'] );
				}
			}
		} );
	}

	public function resolve( Request $request ) {
		$stats = array_filter( [
			'Reads' => $this->hits + $this->misses,
			'Hits' => $this->hits,
			'Misses' => $this->misses,
			'Writes' => $this->writes,
			'Deletes' => $this->deletes,
		] );

		if ( count( $stats ) > 0 ) {
			$request->userData( 'Caching' )->counters( $stats );
		}

		return $request;
	}

	public function hit( int $amount = 1 ) {
		$this->hits += $amount;

		return $this;
	}

	public function miss( int $amount = 1 ) {
		$this->misses += $amount;

		return $this;
	}

	public function write( int $amount = 1 ) {
		$this->writes += $amount;

		return $this;
	}

	public function delete( int $amount = 1 ) {
		$this->deletes += $amount;

		return $this;
	}
}
