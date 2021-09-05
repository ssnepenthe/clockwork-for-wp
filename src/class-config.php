<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

// @todo Consider supporting read-only values.
final class Config {
	private $values = [];

	public function __construct( array $values = [] ) {
		$this->values = $values;
	}

	public function get( $path, $default = null ) {
		return array_get( $this->values, $path, $default );
	}

	public function has( $path ) {
		return array_has( $this->values, $path );
	}

	public function set( $path, $value = null ): void {
		array_set( $this->values, $path, $value );

		// ???
		// return $this;
	}
}
