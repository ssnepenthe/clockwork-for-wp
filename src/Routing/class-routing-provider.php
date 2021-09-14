<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use Clockwork_For_Wp\Base_Provider;
use Invoker\Invoker;

final class Routing_Provider extends Base_Provider {
	// @todo Configurable prefix?
	private const PREFIX = 'cfw_';

	public function register(): void {
		require_once $this->plugin['dir'] . '/src/Routing/helpers.php';

		$this->plugin[ Fastroute_Converter::class ] = static function () {
			return new Fastroute_Converter( self::PREFIX );
		};

		$this->plugin[ Route_Collection::class ] = function () {
			return new Route_Collection( $this->plugin[ Fastroute_Converter::class ] );
		};

		$this->plugin[ Route_Handler_Invoker::class ] = function () {
			return new Route_Handler_Invoker(
				$this->plugin[ Invoker::class ],
				self::PREFIX,
				function ( Route $route ) {
					$params = [];

					foreach ( $route->get_query_vars() as $param_name ) {
						/** @var Route_Handler_Invoker $this */
						$key = $this->strip_param_prefix( $param_name );

						$params[ $key ] = \get_query_var( $param_name );
					}

					return \array_filter(
						$params,
						static function ( $param ) {
							return null !== $param;
						}
					);
				}
			);
		};

		$this->plugin[ Routing_Subscriber::class ] = static function () {
			return new Routing_Subscriber();
		};
	}

	protected function subscribers(): array {
		return [ Routing_Subscriber::class ];
	}
}
