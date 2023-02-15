<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use ApheleiaCli\CommandRegistry;
use ApheleiaCli\InvokerBackedInvocationStrategy;
use Clockwork_For_Wp\Base_Provider;
use Invoker\Invoker;

/**
 * @internal
 */
final class Wp_Cli_Provider extends Base_Provider {
	public function registered(): void {
		if ( ! ( \defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		$registry = new CommandRegistry(
			new InvokerBackedInvocationStrategy( $this->plugin->get_pimple()[ Invoker::class ] )
		);

		$registry->group(
			'clockwork',
			'Manages the Clockwork for WP plugin',
			static function( CommandRegistry $registry ) {
				$registry->add( new Clean_Command() );
				$registry->add( new Generate_Command_List_Command() );
				$registry->add( new Web_Install_Command() );
				$registry->add( new Web_Uninstall_Command() );
			}
		);

		$registry->initialize();
	}
}
