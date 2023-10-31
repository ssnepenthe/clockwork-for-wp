<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Incoming_Request;
use Clockwork_For_Wp\Plugin;

use function Clockwork_For_Wp\service;

/**
 * @internal
 */
final class Routing_Provider extends Base_Provider {
	public function boot( Plugin $plugin ): void {
		$pimple = $plugin->get_pimple();

		$pimple[ Event_Manager::class ]->attach(
			new Routing_Subscriber(
				$pimple[ Route_Collection::class ],
				$pimple[ Route_Handler_Invoker::class ],
				$pimple[ Incoming_Request::class ]
			)
		);
	}

	public function register( Plugin $plugin ): void {
		$pimple = $plugin->get_pimple();

		$pimple[ Route_Collection::class ] = static function () {
			// @todo Configurable prefix?
			return new Route_Collection( 'cfw_' );
		};

		$pimple[ Route_Handler_Invoker::class ] = function () {
			return new Route_Handler_Invoker(
				// @todo Configurable prefix?
				'cfw_',
				function ( Route $route ) {
					$params = [];

					foreach ( $route->get_query_vars() as $param_name ) {
						/** @var Route_Handler_Invoker $this */
						$key = $this->strip_param_prefix( $param_name );

						$params[ $key ] = \get_query_var( $param_name );
					}

					return $params;
				},
				static function ( array $callable ) {
					return [ service( $callable[0] ), $callable[1] ];
				}
			);
		};
	}
}
