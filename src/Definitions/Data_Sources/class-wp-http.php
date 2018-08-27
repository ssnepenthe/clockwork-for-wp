<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Wp_Http as Wp_Http_Data_Source;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface as Toggling_Definition;
use Clockwork_For_Wp\Definitions\Subscribing_Definition_Interface as Subscribing_Definition;

class Wp_Http extends Definition implements Subscribing_Definition, Toggling_Definition {
	public function get_identifier() {
		return 'data_sources.wp_http';
	}

	public function get_subscribed_events() {
		// @todo Should these trigger later than 10?
		return [
			[ 'http_request_args', 'on_http_request_args', Plugin::DEFAULT_EVENT, 2 ],
			[ 'pre_http_request',  'on_pre_http_request',  Plugin::DEFAULT_EVENT, 3 ],
			[ 'http_api_debug',    'on_http_api_debug',    Plugin::DEFAULT_EVENT, 5 ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Wp_Http_Data_Source();
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'wp_http' );
	}
}
