<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Api;

use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Routing\Route_Collection;

final class Api_Subscriber implements Subscriber {
	protected $plugin;

	protected $routes;

	public function __construct( Plugin $plugin, Route_Collection $routes ) {
		$this->plugin = $plugin;
		$this->routes = $routes;
	}

	public function get_subscribed_events(): array {
		return [
			'init' => 'register_routes',
		];
	}

	public function register_routes(): void {
		$this->routes->post(
			'^__clockwork/auth$',
			'index.php?auth=1',
			[ Api_Controller::class, 'authenticate' ]
		);
		$this->routes->get(
			'^__clockwork/([0-9-]+|latest)/extended$',
			'index.php?id=$matches[1]&extended=1',
			[ Api_Controller::class, 'serve_json' ]
		);

		if ( $this->plugin->is()->collecting_client_metrics() ) {
			$this->routes->put(
				'^__clockwork/([0-9-]+)$',
				'index.php?id=$matches[1]&update=1',
				[ Api_Controller::class, 'update_data' ]
			);
	}

		$this->routes->get(
			'^__clockwork/([0-9-]+|latest)(?:/(next|previous))?(?(2)/(\d+))?$',
			'index.php?id=$matches[1]&direction=$matches[2]&count=$matches[3]',
			[ Api_Controller::class, 'serve_json' ]
		);
	}
}
