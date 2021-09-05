<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Subscriber;
use WP_REST_Server;
use function Clockwork_For_Wp\describe_callable;
use function Clockwork_For_Wp\prepare_rest_route;

final class Rest_Api extends DataSource implements Subscriber {
	private $routes = [];

	public function add_route( $path, $methods, $callback = null, $permission_callback = null ) {
		if ( \is_array( $methods ) ) {
			$methods = \implode( ', ', $methods );
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

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => function ( WP_REST_Server $wp_rest_server ): void {
				// @todo Option for core rest endpoints to be filtered from list.
				// @todo Option for what route fields get recorded.
				foreach ( $wp_rest_server->get_routes() as $path => $handlers ) {
					foreach ( $handlers as $handler ) {
						[ $methods, $callback, $permission_callback ] = prepare_rest_route( $handler );

						$this->add_route( $path, $methods, $callback, $permission_callback );
					}
				}
			},
		];
	}

	public function resolve( Request $request ) {
		if ( \count( $this->routes ) > 0 ) {
			$request->userData( 'Routing' )->table( 'REST Routes', $this->routes );
		}

		return $request;
	}
}
