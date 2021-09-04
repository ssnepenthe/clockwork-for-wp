<?php

namespace Clockwork_For_Wp\Event_Management;

use Clockwork_For_Wp\Base_Provider;
use Invoker\Invoker;

class Event_Management_Provider extends Base_Provider {
	public function register() {
		$this->plugin[ Event_Manager::class ] = function () {
			return new Event_Manager( $this->plugin[ Invoker::class ] );
		};
	}
}
