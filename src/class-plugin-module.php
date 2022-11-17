<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Daedalus\Configuration\Events\PreparingBaseSchemas;
use Daedalus\Configuration\Events\SettingConfigurationValues;
use Daedalus\Pimple\Events\AddingContainerDefinitions;
use Daedalus\Plugin\Events\ManagingSubscribers;
use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
final class Plugin_Module implements ModuleInterface {
	public function register( PluginInterface $plugin ): void {
		require_once __DIR__ . '/plugin-helpers.php';
		require_once __DIR__ . '/wordpress-helpers.php';

		$eventDispatcher = $plugin->getEventDispatcher();

		$eventDispatcher->addListener(
			ManagingSubscribers::class,
			[ $this, 'onManagingSubscribers' ]
		);
		$eventDispatcher->addListener(
			AddingContainerDefinitions::class,
			[ $this, 'onAddingContainerDefinitions' ]
		);
		$eventDispatcher->addListener(
			PreparingBaseSchemas::class,
			[ $this, 'onPreparingBaseSchemas' ]
		);
		$eventDispatcher->addListener(
			SettingConfigurationValues::class,
			[ $this, 'onSettingConfigurationValues' ]
		);
	}

	public function onManagingSubscribers( ManagingSubscribers $event ): void {
		$support = $event->assertPluginIsAvailable()
			->getPlugin()
			->getContainer()
			->get( Clockwork_Support::class );

		if ( $support->is_enabled() || $support->is_web_enabled() || $support->is_web_installed() ) {
			$event->addSubscriber( Plugin_Subscriber::class );
		}
	}

	public function onPreparingBaseSchemas( PreparingBaseSchemas $event ): void {
		$plugin = $event->assertPluginIsAvailable()->getPlugin();

		// @todo ?
		// $event->loadBaseSchemaFromFile( "{$plugin->getDir()}/config/schema.php" );

		$schemaPath = "{$plugin->getDir()}/config/schema.php";
		$schema = include $schemaPath;

		$event->setBaseSchemas( $schema );
	}

	public function onSettingConfigurationValues( SettingConfigurationValues $event ): void {
		$plugin = $event->assertPluginIsAvailable()->getPlugin();

		// @todo ?
		// $event->loadDefaultsFromFile( "{$plugin->getDir()}/config/defaults.php" );

		$defaultsPath = "{$plugin->getDir()}/config/defaults.php";
		$defaults = include $defaultsPath;

		$event->mergeValues( $defaults );

		$plugin->getEventManager()->trigger(
			'cfw_config_init',
			// @todo type mismatch between private config and event config?
			new Private_Schema_Configuration( $event->getConfiguration() )
		);
	}

	public function onAddingContainerDefinitions( AddingContainerDefinitions $event ): void {
		$event->addDefinitions( [
			InvokerInterface::class => static function ( ContainerInterface $container ) {
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
		] );
	}
}
