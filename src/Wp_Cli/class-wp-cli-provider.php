<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use ApheleiaCli\CommandRegistry;
use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Plugin;

/**
 * @internal
 */
final class Wp_Cli_Provider extends Base_Provider {
	public function registered( Plugin $plugin ): void {
		if ( ! ( \defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		$registry = new CommandRegistry();

		$registry->group(
			'clockwork',
			'Manages the Clockwork for WP plugin',
			static function( CommandRegistry $registry ) use ( $plugin ) {
				$registry->add( new Clean_Command( $plugin->get_pimple() ) );
				$registry->add( new Generate_Command_List_Command() );
				$registry->add( new Web_Install_Command( $plugin->is() ) );
				$registry->add( new Web_Uninstall_Command( $plugin->is() ) );
			}
		);

		$registry->initialize();
	}
}
