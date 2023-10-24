<?php

declare(strict_types=1);

use Clockwork\Clockwork;

use function Clockwork_For_Wp\service;

if ( ! \function_exists( 'clock' ) ) {
	// Log a message to Clockwork, returns Clockwork instance when called with no arguments, first argument otherwise.
	function clock( ...$arguments ) {
		if ( empty( $arguments ) ) {
			return service( Clockwork::class );
		}

		foreach ( $arguments as $argument ) {
			service( Clockwork::class )->debug( $argument );
		}

		return \reset( $arguments );
	}
}
