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
		// @todo First when ->is_collecting_data(), second when ->is_enabled().
		return [
			[ 'shutdown',  'finalize_request' ],
			[ 'wp_loaded', 'send_headers'     ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Request_Helper( $container['clockwork'], $container['config'] );
		};
	}

	public function is_enabled() {
		// @todo See note above.
		return $this->plugin->service( 'config' )->is_collecting_data(); // or is_enabled()?
	}
}
