<?php

namespace Clockwork_For_Wp\Api;

use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Routing\Route_Collection;

class Api_Subscriber implements Subscriber {
	public function get_subscribed_events() : array {
		return [
			'init' => 'register_routes',
		];
	}

	public function register_routes( Route_Collection $routes ) {
		$routes->post(
			'__clockwork\/auth',
			'index.php?cfw_auth=1',
			[ Api_Controller::class, 'authenticate' ]
		);
		$routes->get(
			'__clockwork\/([0-9-]+|latest)\/extended',
			'index.php?cfw_id=$matches[1]&cfw_extended=1',
			[ Api_Controller::class, 'serve_json' ]
		);
		$routes->get(
			'__clockwork\/([0-9-]+|latest)(?:\/(next|previous))?(?(2)\/(\d+))?',
			'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw_count=$matches[3]',
			[ Api_Controller::class, 'serve_json' ]
		);
	}
}
