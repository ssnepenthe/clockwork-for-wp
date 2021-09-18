<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Closure;
use ReflectionFunction;

/**
 * Adapted from Illuminate\Support\Arr.
 *
 * @param null|mixed $default
 */
function array_get( array $array, string $path, $default = null ) {
	if ( \array_key_exists( $path, $array ) ) {
		return $array[ $path ];
	}

	if ( false === \mb_strpos( $path, '.' ) ) {
		return $default;
	}

	foreach ( \explode( '.', $path ) as $segment ) {
		if ( \is_array( $array ) && \array_key_exists( $segment, $array ) ) {
			$array = $array[ $segment ];
		} else {
			return $default;
		}
	}

	return $array;
}

function array_has( array $array, string $path ) {
	$default = '__CFW__hopefully_unique_enough_default__CFW__';

	return array_get( $array, $path, $default ) !== $default;
}

function array_only( $array, $keys ) {
	return \array_intersect_key( $array, \array_flip( $keys ) );
}

/**
 * Adapted from Illuminate\Support\Arr.
 */
function array_set( array &$array, string $path, $value ) {
	$segments = \explode( '.', $path );

	foreach ( $segments as $i => $segment ) {
		if ( 1 === \count( $segments ) ) {
			break;
		}

		unset( $segments[ $i ] );

		if ( ! isset( $array[ $segment ] ) || ! \is_array( $array[ $segment ] ) ) {
			$array[ $segment ] = [];
		}

		$array = &$array[ $segment ];
	}

	$array[ \array_shift( $segments ) ] = $value;

	return $array;
}

// @todo Name? Or maybe just merge into describe_callable()?
function describe_unavailable_callable( $unavailable_callable ) {
	// WordPress doesn't always load all files for all requests.
	// For this reason we will often be working with callables that haven't been loaded...
	// I.e. is_callable( $unavailable_callable ) === false.
	if ( \is_string( $unavailable_callable ) && '' !== \trim( $unavailable_callable ) ) {
		return "{$unavailable_callable}()";
	}

	if ( \is_array( $unavailable_callable ) ) {
		$class = $unavailable_callable[0] ?? '';
		$method = $unavailable_callable[1] ?? '';

		if (
			\is_string( $class )
			&& '' !== \trim( $class )
			&& \is_string( $method )
			&& '' !== \trim( $method )
		) {
			return "{$class}::{$method}()";
		}
	}

	return '(Unknown)';
}

function describe_callable( $callable ): string {
	if ( ! \is_callable( $callable ) ) {
		return '(Non-callable value)';
	}

	if ( \is_string( $callable ) ) {
		return "{$callable}()";
	}

	if ( \is_array( $callable ) && 2 === \count( $callable ) ) {
		// @todo Should we verify shape of array (0 and 1 indices exist)?
		if ( \is_object( $callable[0] ) ) {
			$class = \get_class( $callable[0] );

			return "{$class}->{$callable[1]}()";
		}
		if ( \is_string( $callable[0] ) ) {
			return "{$callable[0]}::{$callable[1]}()";
		}
	}

	if ( $callable instanceof Closure ) {
		$reflection = new ReflectionFunction( $callable );
		// @todo Configurable set of directories to strip from the fron of filename.
		// $filename = str_replace( ABSPATH, '', $reflection->getFileName() );

		return "Closure ({$reflection->getFileName()}, line {$reflection->getStartLine()})";
	}

	if ( \is_object( $callable ) && \method_exists( $callable, '__invoke' ) ) {
		return describe_callable( [ $callable, '__invoke' ] );
	}

	return '(Unknown)';
}

// @todo Should we be forcing string or can it be any scalar value?
// @todo Should we skip this in favor of a simple json_encode?
function describe_value( $value ): string {
	if ( null === $value ) {
		return 'NULL';
	}

	if ( \is_bool( $value ) ) {
		return $value ? 'TRUE' : 'FALSE';
	}

	if ( \is_string( $value ) ) {
		return "\"{$value}\"";
	}

	if ( \is_numeric( $value ) ) {
		return (string) $value;
	}

	// @todo More specificity when array or resource.
	return '(NON-SCALAR VALUE)';
}
