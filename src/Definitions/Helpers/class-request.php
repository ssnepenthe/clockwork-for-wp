<?php

namespace Clockwork_For_Wp\Definitions\Helpers;

use Pimple\Container;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Request_Helper;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface as Toggling_Definition;
use Clockwork_For_Wp\Definitions\Subscribing_Definition_Interface as Subscribing_Definition;

class Request extends Definition implements Subscribing_Definition, Toggling_Definition {
	public function get_identifier() {
		return 'helpers.request';
	}

	public function get_subscribed_events() {
		return [
			[ 'shutdown', 'finalize_request', Plugin::LATE_EVENT ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Request_Helper( $this->plugin );
		};
	}

	public function is_enabled() {
		return $this->plugin->is_collecting_data();
	}
}
