<?php

namespace Clockwork_For_Wp;

class Config {
	protected $values = [];

	public function __construct( array $values = [] ) {
		$this->values = $values;
	}

	public function get( $path, $default = null ) {
		return array_get( $this->values, $path, $default );
	}

	public function has( $path ) {
		return array_has( $this->values, $path );
	}

	public function set( $path, $value = null ) {
		array_set( $this->values, $path, $value );

		// ???
		// return $this;
	}
}
