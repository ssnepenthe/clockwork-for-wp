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
		$routes->post( '__clockwork/auth', [ Api_Controller::class, 'authenticate' ] );

		if ( $plugin->is_collecting_client_metrics() ) {
			$routes->put( '__clockwork/{id:[0-9-]+}', [ Api_Controller::class, 'update_data' ] );
		}

		$routes->get(
			'__clockwork/{id:[0-9-]+|latest}/extended',
			[ Api_Controller::class, 'serve_json' ]
		);
		$routes->get(
			'__clockwork/{id:[0-9-]+|latest}[/{direction:next|previous}[/{count:\d+}]]',
			[ Api_Controller::class, 'serve_json' ]
		);
	}
}
