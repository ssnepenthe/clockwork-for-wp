<?php

namespace Clockwork_For_Wp;

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
	$start = isset( $query_array[3] ) ? $query_array[3] : microtime( true ) - ( $duration / 1000 );

	return [ $query, $duration, $start ];
}

function wp_error_to_array( \WP_Error $error ) : array {
	return [
		'errors' => $error->errors,
		'error_data' => $error->error_data,
	];
}
