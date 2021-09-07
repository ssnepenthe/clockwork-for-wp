<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use function Clockwork_For_Wp\describe_value;

final class Constants extends DataSource {
	private $constants = [];

	public function __construct( string ...$constants ) {
		$this->constants = $constants;
	}

	public function add_constant( string $constant ) {
		$this->constants[] = $constant;

		return $this;
	}

	public function resolve( Request $request ) {
		$request->userData( 'WordPress' )->table( 'Constants', $this->build_table() );

		return $request;
	}

	public function set_constants( string ...$constants ) {
		$this->constants = $constants;

		return $this;
	}

	private function build_table() {
		// @todo Sort alphabetically?
		return \array_map(
			static function ( $constant ) {
				$value = \defined( $constant )
					? describe_value( \constant( $constant ) )
					: '(NOT DEFINED)';

				return [
					'Name' => $constant,
					'Value' => $value,
				];
			},
			$this->constants
		);
	}
}
