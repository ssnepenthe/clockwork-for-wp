<?php

namespace Clockwork_For_Wp\Web_App;

use Clockwork\Web\Web;
use Clockwork_For_Wp\Base_Provider;

class Web_App_Provider extends Base_Provider {
	public function boot() {
		if ( $this->plugin->is_web_enabled() ) {
			parent::boot();
		}
	}

	public function register() {
		$this->plugin[ Web_App_Controller::class ] = function() {
			return new Web_App_Controller( new Web, $this->plugin[ \WP_Query::class ] );
		};

		$this->plugin[ Web_App_Subscriber::class ] = function() {
			return new Web_App_Subscriber();
		};
	}

	protected function subscribers() : array {
		return [ Web_App_Subscriber::class ];
	}
}
