<?php

namespace Clockwork_For_Wp\Definitions\Helpers;

use Pimple\Container;
use Clockwork_For_Wp\Request_Helper;
use Clockwork_For_Wp\Definitions\Definition;

class Request extends Definition {
	public function get_identifier() {
		return 'helpers.request';
	}

	public function get_subscribed_events() {
		return [
			[ 'shutdown',  'finalize_request' ],
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
