<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Subscriber\Wp_Object_Cache_Subscriber;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Provides_Subscriber;

final class Wp_Object_Cache extends DataSource implements Provides_Subscriber {
	private $deletes = 0;

	private $hits = 0;

	private $misses = 0;

	private $writes = 0;

	public function create_subscriber(): Subscriber {
		return new Wp_Object_Cache_Subscriber( $this );
	}

	public function delete( int $amount = 1 ) {
		$this->deletes += $amount;

		return $this;
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
