<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use ApheleiaCli\InvokerBackedInvocationStrategy;
use Daedalus\ApheleiaCli\Events\AddingCommands;
use Daedalus\ApheleiaCli\Events\CreatingCommandRegistry;
use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use Invoker\InvokerInterface;

/**
 * @internal
 */
final class Wp_Cli_Module implements ModuleInterface {
	public function register( PluginInterface $plugin ): void {
		$eventDispatcher = $plugin->getEventDispatcher();

		$eventDispatcher->addListener( AddingCommands::class, [ $this, 'onAddingCommands' ] );
		$eventDispatcher->addListener( CreatingCommandRegistry::class, [ $this, 'onCreatingCommandRegistry' ] );
	}

	public function onAddingCommands( AddingCommands $event ): void {
		$event->group(
			'clockwork',
			'Manages the Clockwork for WP plugin',
			function ( AddingCommands $event ) {
				$event->addCommands( [
					new Clean_Command(),
					new Generate_Command_List_Command(),
					new Web_Install_Command(),
					new Web_Uninstall_Command(),
				] );
			}
		);
	}

	public function onCreatingCommandRegistry( CreatingCommandRegistry $event ): void {
		$invoker = $event->assertPluginIsAvailable()
			->getPlugin()
			->getContainer()
			->get( InvokerInterface::class );

		$event->setInvocationStrategy( new InvokerBackedInvocationStrategy( $invoker ) );
	}
}
