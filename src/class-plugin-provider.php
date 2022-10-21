<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Daedalus\Pimple\Events\AddingContainerDefinitions;
use Daedalus\Plugin\Events\ManagingSubscribers;
use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use Invoker\Invoker;
use League\Config\Configuration;
use League\Config\ConfigurationBuilderInterface;
use League\Config\ConfigurationInterface;
use Psr\Container\ContainerInterface;
use ToyWpEventManagement\EventManagerInterface;

/**
 * @internal
 */
final class Plugin_Provider implements ModuleInterface {
	public function register( PluginInterface $plugin ): void {
		require_once __DIR__ . '/plugin-helpers.php';
		require_once __DIR__ . '/wordpress-helpers.php';

		$eventDispatcher = $plugin->getEventDispatcher();

		$eventDispatcher->addListener(ManagingSubscribers::class, [$this, 'onManagingSubscribers']);
		$eventDispatcher->addListener(AddingContainerDefinitions::class, [$this, 'onAddingContainerDefinitions']);
	}

	public function onManagingSubscribers( ManagingSubscribers $event ): void {
		$plugin = $event->getPlugin();

		if ($plugin->is_enabled() || $plugin->is_web_enabled() || $plugin->is_web_installed()) {
			$event->addSubscriber(Plugin_Subscriber::class);
		}
	}

	public function onAddingContainerDefinitions( AddingContainerDefinitions $event ): void {
		$event->addDefinitions([
			ConfigurationBuilderInterface::class => static function ( ContainerInterface $container ) {
				$schema = include \dirname( __DIR__ ) . '/config/schema.php';
				$defaults = include \dirname( __DIR__ ) . '/config/defaults.php';

				$config = new Configuration( $schema );

				$config->merge( $defaults );

				$container->get( EventManagerInterface::class )->trigger(
					'cfw_config_init',
					new Private_Schema_Configuration( $config )
				);

				return $config;
			},
			ConfigurationInterface::class => static function ( ContainerInterface $container ) {
				return $container->get( ConfigurationBuilderInterface::class )->reader();
			},
			Invoker::class => function ( ContainerInterface $container ) {
				return $container->get( Plugin::class )->getInvoker();
			},
			Metadata::class => static function ( ContainerInterface $container ) {
				return new Metadata(
					$container->get( Clockwork_Support::class ),
					$container->get( Clockwork::class )->storage()
				);
			},
			Plugin_Subscriber::class => static function () {
				return new Plugin_Subscriber();
			},
		]);
	}
}
