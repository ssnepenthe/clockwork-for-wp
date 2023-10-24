<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use ApheleiaCli\CommandRegistry;
use Clockwork_For_Wp\Base_Provider;

/**
 * @internal
 */
final class Wp_Cli_Provider extends Base_Provider {
	public function registered(): void {
		if ( ! ( \defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		$registry = new CommandRegistry();

		$registry->group(
			'clockwork',
			'Manages the Clockwork for WP plugin',
			function( CommandRegistry $registry ) {
				$registry->add( new Clean_Command( $this->plugin->get_pimple() ) );
				$registry->add( new Generate_Command_List_Command() );
				$registry->add( new Web_Install_Command( $this->plugin ) );
				$registry->add( new Web_Uninstall_Command( $this->plugin ) );
			}
		);

		$registry->initialize();
	}
}
