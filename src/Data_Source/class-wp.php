<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use ToyWpEventManagement\SubscriberInterface;

final class Wp extends DataSource implements SubscriberInterface {
	private $variables = [];

	// @todo Records_Variables trait to share with Wp_Query data source?
	public function add_variable( $var, $value ) {
		$this->variables[] = [
			'Variable' => $var,
			'Value' => $value,
		];

		return $this;
	}

	public function onCfwPreResolve( \WP $wp ): void {
		// @todo Move to rewrite?
		foreach ( [ 'request', 'query_string', 'matched_rule', 'matched_query' ] as $var ) {
			if ( \property_exists( $wp, $var ) && $wp->{$var} ) {
				$this->add_variable( $var, $wp->{$var} );
			}
		}
	}

	public function getSubscribedEvents(): array
	{
		return [
			'cfw_pre_resolve' => 'onCfwPreResolve',
		];
	}

	public function resolve( Request $request ) {
		if ( 0 !== \count( $this->variables ) ) {
			$request->userData( 'WordPress' )->table( 'Request', $this->variables );
		}

		return $request;
	}
}
