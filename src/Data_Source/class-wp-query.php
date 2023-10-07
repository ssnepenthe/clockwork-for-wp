<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Subscriber\Wp_Query_Subscriber;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Provides_Subscriber;

final class Wp_Query extends DataSource implements Provides_Subscriber {
	private $query_vars = [];

	public function add_query_var( $key, $value ) {
		$this->query_vars[ $key ] = [
			'Variable' => $key,
			'Value' => $value,
		];

		return $this;
	}

	public function create_subscriber(): Subscriber {
		return new Wp_Query_Subscriber( $this );
	}

	public function resolve( Request $request ) {
		if ( \count( $this->query_vars ) > 0 ) {
			$vars = $this->query_vars;

			\ksort( $vars );

			$request->userData( 'WordPress' )->table( 'Query Vars', \array_values( $vars ) );
		}

		return $request;
	}

	public function set_query_vars( $vars ) {
		$this->query_vars = [];

		foreach ( $vars as $key => $value ) {
			$this->add_query_var( $key, $value );
		}

		return $this;
	}
}
