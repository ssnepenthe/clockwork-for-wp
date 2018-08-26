<?php

namespace Clockwork_For_Wp\Definitions\Helpers;

use Pimple\Container;
use Clockwork_For_Wp\Api_Helper;
use Clockwork_For_Wp\Definitions\Definition;

class Api extends Definition {
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
