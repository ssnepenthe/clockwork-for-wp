<?php

declare(strict_types=1);

use Clockwork\Clockwork;

if ( ! \function_exists( 'clock' ) ) {
	// Log a message to Clockwork, returns Clockwork instance when called with no arguments, first argument otherwise.
	function clock( ...$arguments ) {
		if ( empty( $arguments ) ) {
			return \_cfw_instance()->get_container()->get( Clockwork::class );
		}

		foreach ( $arguments as $argument ) {
			\_cfw_instance()->get_container()->get( Clockwork::class )->debug( $argument );
		}

		return \reset( $arguments );
	}
}
