<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Cli_Data_Collection;

use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;

final class Cli_Data_Collection_Module implements ModuleInterface {
	public function register( PluginInterface $plugin ): void {
		$plugin->getEventDispatcher()->addListener(PluginLocking::class, [$this, 'onPluginLocking']);
	}

	public function onPluginLocking(): void {
		if ( ! ( \defined( 'WP_CLI' ) &&  WP_CLI ) ) {
			return;
		}

		Cli_Collection_Helper::initialize_logger();
	}
}
