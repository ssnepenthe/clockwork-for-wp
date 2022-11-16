<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Web_App;

use Clockwork\Web\Web;
use Clockwork_For_Wp\Plugin;
use Daedalus\Pimple\Events\AddingContainerDefinitions;
use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use Daedalus\Routing\Events\AddingRoutes;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
final class Web_App_Module implements ModuleInterface {
	public function register( PluginInterface $plugin ): void {
		$eventDispatcher = $plugin->getEventDispatcher();

		$eventDispatcher->addListener(
			AddingContainerDefinitions::class,
			[ $this, 'onAddingContainerDefinitions' ]
		);
		$eventDispatcher->addListener(
			AddingRoutes::class,
			[ $this, 'onAddingRoutes' ]
		);
	}

	public function onAddingContainerDefinitions( AddingContainerDefinitions $event ): void {
		$event->addDefinitions( [
			Web_App_Controller::class => static function ( ContainerInterface $container ) {
				return new Web_App_Controller( new Web(), $container->get( Plugin::class ) );
			},
		] );
	}

	public function onAddingRoutes( AddingRoutes $event ): void {
		$event->loadRoutesFromFile( __DIR__ . '/routes.php' );
	}
}
