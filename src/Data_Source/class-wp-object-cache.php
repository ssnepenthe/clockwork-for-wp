<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Subscriber;

final class Wp_Object_Cache extends DataSource implements Subscriber {
	private $deletes = 0;
	private $hits = 0;
	private $misses = 0;
	private $writes = 0;

	public function delete( int $amount = 1 ) {
		$this->deletes += $amount;

		return $this;
	}

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => function ( \WP_Object_Cache $wp_object_cache ): void {
				// @todo Include hit percentage?
				if ( \property_exists( $wp_object_cache, 'cache_hits' ) ) {
					$this->hit( (int) $wp_object_cache->cache_hits );
				}

				if ( \property_exists( $wp_object_cache, 'cache_misses' ) ) {
					$this->miss( (int) $wp_object_cache->cache_misses );
				}

				if ( \property_exists( $wp_object_cache, 'redis_calls' ) ) {
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
				} elseif ( \property_exists( $wp_object_cache, 'stats' ) ) {
					if ( isset( $wp_object_cache->stats['add'] ) ) {
						$this->write( (int) $wp_object_cache->stats['add'] );
					}

					if ( isset( $wp_object_cache->stats['deletes'] ) ) {
						$this->delete( (int) $wp_object_cache->stats['deletes'] );
					}
				}
			},
		];
	}

	public function hit( int $amount = 1 ) {
		$this->hits += $amount;

		return $this;
	}

	public function miss( int $amount = 1 ) {
		$this->misses += $amount;

		return $this;
	}

	public function resolve( Request $request ) {
		$stats = \array_filter(
			[
				'Reads' => $this->hits + $this->misses,
				'Hits' => $this->hits,
				'Misses' => $this->misses,
				'Writes' => $this->writes,
				'Deletes' => $this->deletes,
			]
		);

		if ( \count( $stats ) > 0 ) {
			$request->userData( 'Caching' )->counters( $stats );
		}

		return $request;
	}

	public function write( int $amount = 1 ) {
		$this->writes += $amount;

		return $this;
	}
}
