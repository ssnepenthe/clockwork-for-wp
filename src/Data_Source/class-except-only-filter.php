<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

final class Except_Only_Filter {
	public function __construct( array $except, array $only ) {
		$this->except = \implode( '|', $except );
		$this->only = \implode( '|', $only );
	}

	public function __invoke( $value ) {
		return $this->passes( $value );
	}

	public function passes( $value ) {
		if ( $this->only ) {
			return 1 === \preg_match( "/{$this->only}/", $value );
		}

		if ( $this->except ) {
			return 1 !== \preg_match( "/{$this->except}/", $value );
		}

		return true;
	}
}
