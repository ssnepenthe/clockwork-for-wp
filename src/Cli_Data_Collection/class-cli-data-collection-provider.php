<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Cli_Data_Collection;

use Clockwork_For_Wp\Base_Provider;

final class Cli_Data_Collection_Provider extends Base_Provider {
	public function registered(): void {
		// @todo Seems like a pretty fragile implementation for collecting commands data... Likely going to need a lot of work.
		if ( ! \defined( 'WP_CLI' ) || ! WP_CLI || ! \class_exists( 'WP_CLI' ) ) {
			return;
		}

		Cli_Collection_Helper::initialize_logger();
	}
}
