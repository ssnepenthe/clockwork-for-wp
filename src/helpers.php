<?php

namespace Clockwork_For_Wp;

/**
 * Adapted from Illuminate\Support\Arr.
 *
 * @todo  Only used by config - consider inlining?
 */
function array_get( array $array, string $path, $default = null ) {
	if ( array_key_exists( $path, $array ) ) {
		return $array[ $path ];
	}

	if ( false === strpos( $path, '.' ) ) {
		return $default;
	}

	foreach ( explode( '.', $path ) as $segment ) {
		if ( is_array( $array ) && array_key_exists( $segment, $array ) ) {
			$array = $array[ $segment ];
		} else {
			return $default;
		}
	}

	return $array;
}

function array_has( array $array, string $path ) {
	$default = '__CFW__hopefully_unique_enough_default__CFW__';

	return $default !== array_get( $array, $path, $default );
}

/**
 * Adapted from Illuminate\Support\Arr.
 */
function array_set( array &$array, string $path, $value ) {
	$segments = explode( '.', $path );

	foreach ( $segments as $i => $segment ) {
		if ( count( $segments ) === 1 ) {
			break;
		}

		unset( $segments[ $i ] );

		if ( ! isset( $array[ $segment ] ) || ! is_array( $array[ $segment ] ) ) {
			$array[ $segment ] = [];
		}

		$array =& $array[ $segment ];
	}

	$array[ array_shift( $segments ) ] = $value;

	return $array;
}

function describe_callable( $callable ) {
	if ( ! is_callable( $callable ) ) {
		return '(Non-callable value)';
	}

	if ( is_string( $callable ) ) {
		return "{$callable}()";
	}

	if ( is_array( $callable ) && 2 === count( $callable ) ) {
		// @todo Should we verify shape of array (0 and 1 indices exist)?
		if ( is_object( $callable[0] ) ) {
			$class = get_class( $callable[0] );

			return "{$class}->{$callable[1]}()";
		} elseif ( is_string( $callable[0] ) ) {
			return "{$callable[0]}::{$callable[1]}()";
		}
	}

	if ( $callable instanceof \Closure ) {
		$reflection = new \ReflectionFunction( $callable );
		// @todo Configurable set of directories to strip from the fron of filename.
		// $filename = str_replace( ABSPATH, '', $reflection->getFileName() );

		return "Closure ({$reflection->getFileName()}, line {$reflection->getStartLine()})";
	}

	if ( is_object( $callable ) && method_exists( $callable, '__invoke' ) ) {
		return describe_callable( [ $callable, '__invoke' ] );
	}

	return '(Unknown)';
}

// @todo Should we be forcing string or can it be any scalar value?
// @todo Should we skip this in favor of a simple json_encode?
function describe_value( $value ) {
	if ( null === $value ) {
		return 'NULL';
	}

	if ( is_bool( $value ) ) {
		return $value ? 'TRUE' : 'FALSE';
	}

	if ( is_string( $value ) ) {
		return "\"{$value}\"";
	}

	if ( is_numeric( $value ) ) {
		return (string) $value;
	}

	// @todo More specificity when array or resource.
	return '(NON-SCALAR VALUE)';
}

// @todo Create dedicated files for wordpress-specific helpers?
function prepare_rest_route( array $handlers_array ) {
	// @todo Filter necessary?
	$methods = array_keys( array_filter( $handlers_array['methods'] ) );
	$callback = $handlers_array['callback'] ?? null;
	$permission_callback = $handlers_array['permission_callback'] ?? null;

	return [ $methods, $callback, $permission_callback ];
}

function prepare_http_response( $response ) {
	if ( \is_wp_error( $response ) ) {
		return [
			'error' => wp_error_to_array( $response ),
			'response' => null,
		];
	}

	$headers = \wp_remote_retrieve_headers( $response );

	if ( $headers instanceof \Requests_Utility_CaseInsensitiveDictionary ) {
		$headers = $headers->getAll();
	} else {
		$headers = [];
	}

	return [
		'error' => null,
		'response' => [
			'body' => \wp_remote_retrieve_body( $response ),
			// @todo Cookies is an array of cookie objects - should we convert to arrays?
			'cookies' => \wp_remote_retrieve_cookies( $response ),
			'headers' => $headers,
			'status' => \wp_remote_retrieve_response_code( $response ),
		],
	];
}

function prepare_wpdb_query( array $query_array ) : array {
	$query = isset( $query_array[0] ) ? $query_array[0] : '';
	$duration = isset( $query_array[1] ) ? ( $query_array[1] * 1000 ) : 0;

	return [ $query, $duration ];
}

function wp_error_to_array( \WP_Error $error ) : array {
	return [
		'errors' => $error->errors,
		'error_data' => $error->error_data,
	];
}
