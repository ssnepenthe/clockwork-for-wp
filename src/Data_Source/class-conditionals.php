<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use function Clockwork_For_Wp\describe_callable;
use function Clockwork_For_Wp\describe_value;

final class Conditionals extends DataSource {
	private $conditionals = [];

	public function __construct( callable ...$conditionals ) {
		$this->conditionals = $conditionals;
	}

	public function add_conditional( callable $conditional ) {
		// @todo Check for duplicates?
		$this->conditionals[] = $conditional;

		return $this;
	}

	public function resolve( Request $request ) {
		$request->userData( 'WordPress' )->table( 'Conditionals', $this->build_table() );

		return $request;
	}

	public function set_conditionals( callable ...$conditionals ) {
		$this->conditionals = $conditionals;

		return $this;
	}

	private function build_table() {
		$table = \array_map(
			static function ( $callable ) {
				return [
					'Function' => describe_callable( $callable ),
					'Value' => describe_value( (bool) $callable() ),
				];
			},
			$this->conditionals
		);

		\usort(
			$table,
			static function ( $a, $b ) {
				if ( $a['Value'] === $b['Value'] ) {
					return \strcmp( $a['Function'], $b['Function'] );
				}

				return 'TRUE' === $a['Value'] ? -1 : 1;
			}
		);

		return $table;
	}
}
