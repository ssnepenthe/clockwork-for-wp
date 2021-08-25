<?php

namespace Clockwork_For_Wp\Wp_Cli;

use Clockwork_For_Wp\Base_Provider;

class Wp_Cli_Provider extends Base_Provider {
	public function register() {
		require_once $this->plugin['dir'] . '/src/Wp_Cli/helpers.php';

		$this->plugin[ Cli_Collection_Helper::class ] = function() {
			return new Cli_Collection_Helper();
		};
	}

	public function registered() {
		// @todo Seems like a pretty fragile implementation for collecting commands... Likely going to need a lot of work.
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! class_exists( 'WP_CLI' ) ) {
			return;
		}

		$this->plugin[ Cli_Collection_Helper::class ]->initialize_logger();

		add_command( new Clean_Command() );
		add_command( new Generate_Command_Lists_Command() );
	}
}
