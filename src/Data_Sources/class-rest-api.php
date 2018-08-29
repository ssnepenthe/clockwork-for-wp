<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Rest_Api extends DataSource {
	protected $wp_rest_server;

	public function __construct( $wp_rest_server = null ) {
		$this->set_wp_rest_server( $wp_rest_server );
	}

	public function get_wp_rest_server() {
		return $this->wp_rest_server;
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'Routing' );

		$panel->table( 'REST Routes', $this->routes_table() );

		return $request;
	}

	protected function routes_table() {
		if (
			null === $this->wp_rest_server
			|| ! method_exists( $this->wp_rest_server, 'get_routes' )
		) {
			return [];
		}

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

	public function set_wp_rest_server( $wp_rest_server ) {
		$this->wp_rest_server = is_object( $wp_rest_server ) ? $wp_rest_server : null;
	}
}
