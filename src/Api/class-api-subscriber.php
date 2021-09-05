<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Api;

use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Routing\Route_Collection;

final class Api_Subscriber implements Subscriber {
	public function get_subscribed_events(): array {
		return [
			'init' => 'register_routes',
		];
	}

	public function register_routes( Route_Collection $routes, Plugin $plugin ): void {
		$routes->post(
			'__clockwork\/auth',
			'index.php?auth=1',
			[ Api_Controller::class, 'authenticate' ]
		);
		$routes->get(
			'__clockwork\/([0-9-]+|latest)\/extended',
			'index.php?id=$matches[1]&extended=1',
			[ Api_Controller::class, 'serve_json' ]
		);

		if ( $plugin->is_collecting_client_metrics() ) {
			$routes->put(
				'__clockwork\/([0-9-]+)',
				'index.php?id=$matches[1]&update=1',
				[ Api_Controller::class, 'update_data' ]
			);
		}

		$routes->get(
			'__clockwork\/([0-9-]+|latest)(?:\/(next|previous))?(?(2)\/(\d+))?',
			'index.php?id=$matches[1]&direction=$matches[2]&count=$matches[3]',
			[ Api_Controller::class, 'serve_json' ]
		);
	}
}
