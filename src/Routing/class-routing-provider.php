<?php

namespace Clockwork_For_Wp\Routing;

use Clockwork_For_Wp\Base_Provider;

class Routing_Provider extends Base_Provider {
	public function register() {
		$this->plugin[ Route_Collection::class ] = function() {
			return new Route_Collection();
		};

		$this->plugin[ Routing_Subscriber::class ] = function() {
			return new Routing_Subscriber();
		};
	}

	protected function subscribers() : array {
		return [ Routing_Subscriber::class ];
	}
}
