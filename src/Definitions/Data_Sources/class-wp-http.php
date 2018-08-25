<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Wp_Http as Wp_Http_Data_Source;

class Wp_Http extends Definition {
	public function get_identifier() {
		return 'data_sources.wp_http';
	}

	public function get_subscribed_events() {
		// @todo Should these trigger later than 10?
		return [
			[ 'http_request_args', 'on_http_request_args', 10, 2 ],
			[ 'pre_http_request',  'on_pre_http_request',  10, 3 ],
			[ 'http_api_debug',    'on_http_api_debug',    10, 5 ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Wp_Http_Data_Source();
		};
	}

	public function is_enabled() {
		// @todo
		return true;
	}
}
