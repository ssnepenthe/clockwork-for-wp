<?php

namespace Clockwork_For_Wp\Routing;

use Clockwork_For_Wp\Base_Provider;
use Invoker\Invoker;

class Routing_Provider extends Base_Provider {
	public function register() {
		$this->plugin[ Route_Collection::class ] = function () {
			// @todo Configurable prefix?
			return new Route_Collection( 'cfw_' );
		};

		$this->plugin[ Route_Handler_Invoker::class ] = function () {
			return new Route_Handler_Invoker(
				$this->plugin[ Invoker::class ],
				// @todo Configurable prefix?
				'cfw_',
				function ( Route $route ) {
					$params = [];

					foreach ( $route->get_query_vars() as $param_name ) {
						/** @var Route_Handler_Invoker $this */
						$key = $this->strip_param_prefix( $param_name );

						$params[ $key ] = get_query_var( $param_name );
					}

					return array_filter( $params, function ( $param ) {
						return null !== $param;
					} );
				}
			);
		};

		$this->plugin[ Routing_Subscriber::class ] = function () {
			return new Routing_Subscriber();
		};
	}

	protected function subscribers(): array {
		return [ Routing_Subscriber::class ];
	}
}
