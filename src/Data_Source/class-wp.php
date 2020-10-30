<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

class Wp extends DataSource implements Subscriber {
	protected $variables = [];

	public function subscribe_to_events( Event_Manager $event_manager ) : void {
		$event_manager->on( 'cfw_pre_resolve_request', function( \WP $wp ) {
			// @todo Move to rewrite?
			foreach ( [ 'request', 'query_string', 'matched_rule', 'matched_query' ] as $var ) {
				if ( property_exists( $wp, $var ) && $wp->{$var} ) {
					$this->add_variable( $var, $wp->{$var} );
				}
			}
		} );
	}

	public function resolve( Request $request ) {
		if ( 0 !== count( $this->variables ) ) {
			$request->userData( 'WordPress' )->table( 'Request', $this->variables );
		}

		return $request;
	}

	// @todo Records_Variables trait to share with Wp_Query data source?
	public function add_variable( $var, $value ) {
		$this->variables[] = [
			'Variable' => $var,
			'Value'    => $value,
		];

		return $this;
	}
}
