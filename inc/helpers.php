<?php

namespace Clockwork_For_Wp;

function callable_to_display_string( callable $callable ) {
	if ( is_string( $callable ) ) {
		return $callable;
	}

	if ( is_array( $callable ) && 2 === count( $callable ) ) {
		if ( is_object( $callable[0] ) ) {
			$class = get_class( $callable[0] );

			return "{$class}->{$callable[1]}";
		} else {
			return "{$callable[0]}::{$callable[1]}";
		}
	}

	if ( $callable instanceof \Closure ) {
		return 'Closure';
	}

	return '(Unknown)';
}
