<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Plugin;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;
use SimpleWpRouting\CallableResolver\PsrContainerCallableResolver;
use SimpleWpRouting\Router;
use WpEventDispatcher\EventDispatcherInterface;

class Routing_Provider extends Base_Provider {
	public function boot( Plugin $plugin ): void {
		$pimple = $plugin->get_pimple();

		$pimple[ EventDispatcherInterface::class ]->addSubscriber(
			new Routing_Subscriber( $pimple[ Router::class ], $pimple[ Route_Loader::class ] )
		);
	}

	public function register( Plugin $plugin ): void {
		$pimple = $plugin->get_pimple();

		$pimple[ Route_Loader::class ] = static function () {
			return new Route_Loader();
		};

		$pimple[ Router::class ] = static function ( Container $pimple ) {
			$router = new Router();

			$router->setPrefix( 'cfw_' );
			$router->setCallableResolver( new PsrContainerCallableResolver( new Psr11Container( $pimple ) ) );
			$router->enableCache( \dirname( $pimple['file'] ) . '/generated' );

			return $router;
		};
	}
}
