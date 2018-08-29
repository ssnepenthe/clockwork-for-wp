<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp extends DataSource {
	protected $wp;

	public function __construct( $wp = null ) {
		$this->set_wp( $wp );
	}

	public function resolve( Request $request ) {
		$table = $this->build_table();

		if ( 0 !== count( $table ) ) {
			$panel = $request->userData( 'WordPress' );

			$panel->table( 'Request', $table );
		}

		return $request;
	}

	public function set_wp( $wp ) {
		$this->wp = is_object( $wp ) ? $wp : null;
	}

	protected function build_table() {
		if ( null === $this->wp ) {
			return [];
		}

		return array_map(
			function( $var ) {
				return [
					'Variable' => $var,
					'Value' => $this->wp->{$var},
				];
			},
			array_filter(
				[ 'request', 'query_string', 'matched_rule', 'matched_query' ],
				function( $var ) {
					return property_exists( $this->wp, $var ) && $this->wp->{$var};
				}
			)
		);
	}
}
