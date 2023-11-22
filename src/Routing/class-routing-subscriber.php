<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use SimpleWpRouting\Router;
use WpEventDispatcher\SubscriberInterface;

class Routing_Subscriber implements SubscriberInterface {
	private Route_Loader $route_loader;

	private Router $router;

	public function __construct( Router $router, Route_Loader $route_loader ) {
		$this->router = $router;
		$this->route_loader = $route_loader;
	}

	public function getSubscribedEvents(): array {
		return [
			'init' => 'on_init',
		];
	}

	public function on_init(): void {
		$this->router->initialize( $this->route_loader );
	}
}
