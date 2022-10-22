<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Api;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Metadata;
use Daedalus\Pimple\Events\AddingContainerDefinitions;
use Daedalus\Plugin\Events\ManagingSubscribers;
use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
final class Api_Module implements ModuleInterface {
	public function register( PluginInterface $plugin ): void {
		$eventDispatcher = $plugin->getEventDispatcher();

		$eventDispatcher->addListener( AddingContainerDefinitions::class, [ $this, 'onAddingContainerDefinitions' ] );
		$eventDispatcher->addListener( ManagingSubscribers::class, [ $this, 'onManagingSubscribers'] );
	}

	public function onAddingContainerDefinitions( AddingContainerDefinitions $event ): void {
		$event->addDefinitions([
			Api_Controller::class => static function ( ContainerInterface $container ) {
				return new Api_Controller(
					$container->get( AuthenticatorInterface::class ),
					$container->get( Metadata::class ),
					$container->get( IncomingRequest::class )
				);
			},
			Api_Subscriber::class => static function () {
				return new Api_Subscriber();
			},
		]);
	}

	public function onManagingSubscribers( ManagingSubscribers $event ): void {
		if (! $event->getPlugin()->is_enabled()) {
			return;
		}

		$event->addSubscribers([
			Api_Subscriber::class,
		]);
	}
}
