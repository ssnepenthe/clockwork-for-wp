<?php

namespace Clockwork_For_Wp\Api;;

use Clockwork\Clockwork;
use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Base_Provider;

class Api_Provider extends Base_Provider {
	public function boot() {
		if ( $this->plugin->is_enabled() ) {
			parent::boot();
		}
	}

	public function register() {
		$this->plugin[ Api_Controller::class ] = function() {
			return new Api_Controller(
				$this->plugin[ Clockwork::class ],
				$this->plugin[ IncomingRequest::class ]
			);
		};

		$this->plugin[ Api_Subscriber::class ] = function() {
			return new Api_Subscriber();
		};
	}

	protected function subscribers() : array {
		return [ Api_Subscriber::class ];
	}
}
