<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use WpEventDispatcher\SubscriberInterface;

class Routing_Subscriber implements SubscriberInterface {
	private Route_Loader $route_loader;

	public function __construct( Route_Loader $route_loader ) {
		$this->route_loader = $route_loader;
	}

	public function getSubscribedEvents(): array {
		return [
			'init' => 'on_init',
		];
	}

	public function on_init(): void {
		$this->route_loader->initialize();
	}
}
