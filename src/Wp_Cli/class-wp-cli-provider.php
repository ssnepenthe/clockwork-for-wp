<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use Clockwork_For_Wp\Base_Provider;

final class Wp_Cli_Provider extends Base_Provider {
	public function register(): void {
		require_once __DIR__ . '/helpers.php';
	}

	public function registered(): void {
		if ( ! \defined( 'WP_CLI' ) || ! WP_CLI || ! \class_exists( 'WP_CLI' ) ) {
			return;
		}

		add_command( new Clean_Command() );
		add_command( new Generate_Command_Lists_Command() );
	}
}
