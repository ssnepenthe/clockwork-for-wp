<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use Daedalus\Pimple\Events\AddingContainerDefinitions;
use Daedalus\Plugin\Events\PluginBooting;
use Daedalus\Plugin\Events\PluginLocking;
use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use Invoker\Invoker;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
final class Wp_Cli_Provider implements ModuleInterface {
	public function register( PluginInterface $plugin ): void {
		$eventDispatcher = $plugin->getEventDispatcher();

		$eventDispatcher->addListener( AddingContainerDefinitions::class, [ $this, 'onAddingContainerDefinitions' ] );
		$eventDispatcher->addListener( PluginBooting::class, [ $this, 'onPluginBooting' ] );
		$eventDispatcher->addListener( PluginLocking::class, [ $this, 'onPluginLocking' ] );
	}

	public function onAddingContainerDefinitions( AddingContainerDefinitions $event ): void {
		$event->addDefinitions([
			Command_Registry::class => static function ( ContainerInterface $container ) {
				return new Command_Registry( $container->get( Invoker::class ) );
			},
		]);
	}

	public function onPluginBooting( PluginBooting $event ): void {
		$event->getPlugin()->getContainer()->get( Command_Registry::class )->initialize();
	}

	public function onPluginLocking( PluginLocking $event ): void {
		if ( ! ( \defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		$event->getPlugin()->getContainer()->get( Command_Registry::class )->namespace(
			'clockwork',
			'Manages the Clockwork for WP plugin.',
			static function ( Command_Registry $scoped_registry ): void {
				$scoped_registry
					->add( new Clean_Command() )
					->add( new Generate_Command_List_Command() )
					->add( new Web_Install_Command() )
					->add( new Web_Uninstall_Command() );
			}
		);
	}
}
