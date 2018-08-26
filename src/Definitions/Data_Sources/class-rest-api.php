<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Rest_Api as Rest_Api_Data_Source;

class Rest_Api extends Definition {
	public function get_identifier() {
		return 'data_sources.rest_api';
	}

	public function get_subscribed_events() {
		// @todo
		return [];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Rest_Api_Data_Source( $container['wp_rest_server'] );
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'rest_api' );
	}
}
