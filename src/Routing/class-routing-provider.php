<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use Daedalus\Pimple\Events\AddingContainerDefinitions;
use Daedalus\Plugin\Events\ManagingSubscribers;
use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use Invoker\Invoker;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
final class Routing_Provider implements ModuleInterface {
	public function register( PluginInterface $plugin ): void {
		$eventDispatcher = $plugin->getEventDispatcher();

		$eventDispatcher->addListener(AddingContainerDefinitions::class, [$this, 'onAddingContainerDefinitions']);
		$eventDispatcher->addListener(ManagingSubscribers::class, [$this, 'onManagingSubscribers']);
	}

	public function onAddingContainerDefinitions( AddingContainerDefinitions $event ): void {
		$event->addDefinitions([
			Route_Collection::class => static function () {
				// @todo Configurable prefix?
				return new Route_Collection( 'cfw_' );
			},
			Route_Handler_Invoker::class => function ( ContainerInterface $container ) {
				return new Route_Handler_Invoker(
					$container->get( Invoker::class ),
					// @todo Configurable prefix?
					'cfw_',
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
			},
			Routing_Subscriber::class => static function () {
				return new Routing_Subscriber();
			},
		]);
	}

	public function onManagingSubscribers( ManagingSubscribers $event ): void {
		$event->addSubscribers([
			Routing_Subscriber::class,
		]);
	}
}
