<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;

use function Clockwork_For_Wp\describe_value;
use function Clockwork_For_Wp\describe_callable;

class Conditionals extends DataSource {
	protected $conditionals = [];

	public function __construct( callable ...$conditionals ) {
		$this->conditionals = $conditionals;
	}

	public function add_conditional( callable $conditional ) {
		// @todo Check for duplicates?
		$this->conditionals[] = $conditional;

		return $this;
	}

	public function set_conditionals( callable ...$conditionals ) {
		$this->conditionals = $conditionals;

		return $this;
	}

	public function resolve( Request $request ) {
		$request->userData( 'WordPress' )->table( 'Conditionals', $this->build_table() );

		return $request;
	}

	protected function build_table() {
		$table = array_map( function( $callable ) {
			return [
				'Function' => describe_callable( $callable ),
				'Value' => describe_value( (bool) call_user_func( $callable ) ),
			];
		}, $this->conditionals );

		usort( $table, function( $a, $b ) {
			if ( $a['Value'] === $b['Value'] ) {
				return strcmp( $a['Function'], $b['Function'] );
			}

			return 'TRUE' === $a['Value'] ? -1 : 1;
		} );

		return $table;
	}
}
