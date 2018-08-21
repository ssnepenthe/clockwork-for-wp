<?php

namespace Clockwork_For_Wp;

function callable_to_display_string( callable $callable ) {
	if ( is_string( $callable ) ) {
		return "{$callable}()";
	}

	if ( is_array( $callable ) && 2 === count( $callable ) ) {
		// @todo Do we need to verify 0 and 1 indices exist? Or can we assume since it is callable?
		if ( is_object( $callable[0] ) ) {
			$class = get_class( $callable[0] );

			return "{$class}->{$callable[1]}()";
		} elseif ( is_string( $callable[0] ) ) {
			return "{$callable[0]}::{$callable[1]}()";
		}
	}

	if ( $callable instanceof \Closure ) {
		$reflection = new \ReflectionFunction( $callable );
		$filename = str_replace( ABSPATH, '', $reflection->getFileName() );

		return "Closure ({$filename}, line {$reflection->getStartLine()})";
	}

	if ( is_object( $callable ) && method_exists( $callable, '__invoke' ) ) {
		return callable_to_display_string( [ $callable, '__invoke' ] );
	}

	return '(Unknown)';
}
