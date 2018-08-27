<?php

namespace Clockwork_For_Wp\Definitions\Helpers;

use Pimple\Container;
use Clockwork_For_Wp\Api_Helper;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface as Toggling_Definition;
use Clockwork_For_Wp\Definitions\Subscribing_Definition_Interface as Subscribing_Definition;

class Api extends Definition implements Subscribing_Definition, Toggling_Definition {
	public function get_identifier() {
		return 'helpers.api';
	}

	public function get_subscribed_events() {
		return [
			[ 'init',              'register_routes' ],
			[ 'template_redirect', 'serve_json'      ],
			[ 'wp_loaded',         'send_headers'    ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Api_Helper( $this->plugin );
		};
	}

	public function is_enabled() {
		return $this->plugin->is_enabled();
	}
}
