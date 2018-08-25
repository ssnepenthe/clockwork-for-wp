<?php

namespace Clockwork_For_Wp\Data_Sources;

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

		return call_user_func_array( 'array_merge', array_map(
			function( $path, $handlers ) {
				return array_map(
					function( $handler ) use ( $path ) {
						// $handler also holds args, accept_json, accept_raw and show_in_index.
						$callback = '';
						$permission_callback = '';
						$methods = implode(
							', ',
							array_keys( array_filter( $handler['methods'] ) )
						);

						if ( isset( $handler['callback'] ) ) {
							$callback = \Clockwork_For_Wp\callable_to_display_string(
								$handler['callback']
							);
						}

						if ( isset( $handler['permission_callback'] ) ) {
							$permission_callback = \Clockwork_For_Wp\callable_to_display_string(
								$handler['permission_callback']
							);
						}

						return [
							'Path' => $path,
							'Methods' => $methods,
							'Callback' => $callback,
							'Permission Callback' => $permission_callback,
						];
					},
					$handlers
				);
			},
			array_keys( $routes ),
			$routes
		) );
	}
}
