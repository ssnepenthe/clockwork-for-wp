<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

use function Clockwork_For_Wp\describe_callable;
use function Clockwork_For_Wp\prepare_rest_route;

class Rest_Api extends DataSource implements Subscriber {
	protected $routes = [];

	public function subscribe_to_events( Event_Manager $event_manager ) : void {
		$event_manager->on( 'cfw_pre_resolve_request', function( \WP_REST_Server $wp_rest_server ) {
			// @todo Option for core rest endpoints to be filtered from list.
			// @todo Option for what route fields get recorded.
			foreach ( $wp_rest_server->get_routes() as $path => $handlers ) {
				foreach ( $handlers as $handler ) {
					[ $methods, $callback, $permission_callback ] = prepare_rest_route( $handler );

					$this->add_route( $path, $methods, $callback, $permission_callback );
				}
			}
		} );
	}

	public function resolve( Request $request ) {
		if ( count( $this->routes ) > 0 ) {
			$request->userData( 'Routing' )->table( 'REST Routes', $this->routes );
		}

		return $request;
	}

	public function add_route( $path, $methods, $callback = null, $permission_callback = null ) {
		if ( is_array( $methods ) ) {
			$methods = implode( ', ', $methods );
		}

		if ( null === $callback ) {
			$callback = '';
		} else {
			$callback = describe_callable( $callback );
		}

		if ( null === $permission_callback ) {
			$permission_callback = '';
		} else {
			$permission_callback = describe_callable( $permission_callback );
		}

		// @todo Filter null values?
		$this->routes[] = [
			'Path' => $path,
			'Methods' => $methods,
			'Callback' => $callback,
			'Permission Callback' => $permission_callback,
		];

		return $this;
	}
}
