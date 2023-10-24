<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Event_Management;

use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Plugin;

/**
 * @internal
 */
final class Event_Management_Provider extends Base_Provider {
	public function register( Plugin $plugin ): void {
		$plugin->get_pimple()[ Event_Manager::class ] = static function () {
			return new Event_Manager();
		};
	}
}
