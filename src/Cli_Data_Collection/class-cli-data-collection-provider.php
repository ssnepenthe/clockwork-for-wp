<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Cli_Data_Collection;

use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Plugin;

final class Cli_Data_Collection_Provider extends Base_Provider {
	public function registered( Plugin $plugin ): void {
		if ( ! ( \defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		Cli_Collection_Helper::initialize_logger();
	}
}
