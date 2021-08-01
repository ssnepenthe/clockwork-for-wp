<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Subscriber;

class Wp_Query extends DataSource implements Subscriber {
	protected $query_vars = [];

	public function get_subscribed_events() : array {
		return [
			'cfw_pre_resolve' => function( \WP_Query $wp_query ) {
				// @todo I think there is a flaw in this logic... It is poorly adapted from query monitor.
				// @todo Move to event manager?
				$plugin_vars = apply_filters( 'query_vars', [] );

				$query_vars = array_filter(
					$wp_query->query_vars,
					function( $value, $key ) use ( $plugin_vars ) {
						return ( isset( $plugin_vars[ $key ] ) && '' !== $value ) || ! empty( $value );
					},
					ARRAY_FILTER_USE_BOTH
				);

				$this->set_query_vars( $query_vars );
			},
		];
	}

	public function resolve( Request $request ) {
		if ( count( $this->query_vars ) > 0 ) {
			$vars = $this->query_vars;

			ksort( $vars );

			$request->userData( 'WordPress' )->table( 'Query Vars', array_values( $vars ) );
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

	public function add_query_var( $key, $value ) {
		$this->query_vars[ $key ] = [
			'Variable' => $key,
			'Value' => $value,
		];

		return $this;
	}
}
