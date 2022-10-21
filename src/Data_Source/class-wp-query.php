<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use ToyWpEventManagement\SubscriberInterface;

final class Wp_Query extends DataSource implements SubscriberInterface {
	private $query_vars = [];

	public function add_query_var( $key, $value ) {
		$this->query_vars[ $key ] = [
			'Variable' => $key,
			'Value' => $value,
		];

		return $this;
	}

	public function onCfwPreResolve( \WP_Query $wp_query ): void {
		// @todo I think there is a flaw in this logic... It is poorly adapted from query monitor.
		// @todo Move to event manager?
		$plugin_vars = \apply_filters( 'query_vars', [] );

		$query_vars = \array_filter(
			$wp_query->query_vars,
			static function ( $value, $key ) use ( $plugin_vars ) {
				return ( isset( $plugin_vars[ $key ] ) && '' !== $value ) || ! empty( $value );
			},
			\ARRAY_FILTER_USE_BOTH
		);

		$this->set_query_vars( $query_vars );
	}

	public function getSubscribedEvents(): array
	{
		return [
			'cfw_pre_resolve' => 'onCfwPreResolve',
		];
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
