<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Web_App;

use Clockwork\Web\Web;
use Daedalus\Pimple\Events\AddingContainerDefinitions;
use Daedalus\Plugin\Events\ManagingSubscribers;
use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use Psr\Container\ContainerInterface;
use WP_Query;

/**
 * @internal
 */
final class Web_App_Provider implements ModuleInterface {
	public function register( PluginInterface $plugin ): void {
		$eventDispatcher = $plugin->getEventDispatcher();

		$eventDispatcher->addListener( AddingContainerDefinitions::class, [ $this, 'onAddingContainerDefinitions' ] );
		$eventDispatcher->addListener( ManagingSubscribers::class, [ $this, 'onManagingSubscribers' ] );
	}

	public function onAddingContainerDefinitions( AddingContainerDefinitions $event ): void {
		$event->addDefinitions([
			Web_App_Controller::class => static function ( ContainerInterface $container ) {
				return new Web_App_Controller( new Web(), $container->get( WP_Query::class ) );
			},
			Web_App_Subscriber::class => static function () {
				return new Web_App_Subscriber();
			},
		]);
	}

	public function onManagingSubscribers( ManagingSubscribers $event ): void {
		$plugin = $event->getPlugin();

		if ($plugin->is_web_enabled() && ! $plugin->is_web_installed()) {
			$event->addSubscriber( Web_App_Subscriber::class );
		}
	}
}
