<?php

namespace Clockwork_For_Wp\Data_Source;

use WP_REST_Server;
use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Rest_Api extends DataSource {
	protected $wp_rest_server;

	public function __construct( WP_REST_Server $wp_rest_server ) {
		$this->wp_rest_server = $wp_rest_server;
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'rest-api' )->title( 'REST API' );

		$panel->table( 'Routes', $this->routes_table() );

		return $request;
	}

	protected function routes_table() {
		$routes = $this->wp_rest_server->get_routes();

		return call_user_func_array( 'array_merge', array_map( function( $path, $handlers ) {
			return array_map( function( $handler ) use ( $path ) {
				$methods = array_keys( array_filter( $handler['methods'] ) );

				return [
					'Path' => $path,
					'Methods' => implode( ', ', $methods ),
					// "Args" => $handler['args'],
					// 'Accept JSON' => isset( $handler['accept_json'] ) && $handler['accept_json']
					// 	? 'Yes'
					// 	: 'No',
					// 'Accept Raw' => isset( $handler['accept_raw'] ) && $handler['accept_raw']
					// 	? 'Yes'
					// 	: 'No',
					// 'Show In Index' => isset( $handler['show_in_index'] ) && $handler['show_in_index']
					// 	? 'Yes'
					// 	: 'No',
					'Callback' => isset( $handler['callback'] )
						? \Clockwork_For_Wp\callable_to_display_string( $handler['callback'] )
						: 'undefined',
					'Permission Callback' => isset( $handler['permission_callback'] )
						? \Clockwork_For_Wp\callable_to_display_string( $handler['permission_callback'] )
						: 'undefined',
				];
			}, $handlers );
		}, array_keys( $routes ), $routes ) );
	}
}
