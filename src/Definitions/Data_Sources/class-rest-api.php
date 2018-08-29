<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface;
use Clockwork_For_Wp\Data_Sources\Rest_Api as Rest_Api_Data_Source;

class Rest_Api extends Definition implements Toggling_Definition_Interface {
	public function get_identifier() {
		return 'data_sources.rest_api';
	}

	public function get_value() {
		return function( Container $container ) {
			$source = new Rest_Api_Data_Source();
			$dep_handler = function() use ( $container, $source ) {
				$source->set_wp_rest_server( $container['wp_rest_server'] );
			};

			// REST Server is actually first initialized on 'parse_request'.
			if ( did_action( 'init' ) ) {
				$dep_handler();
			} else {
				add_action( 'init', $dep_handler );
			}

			return $source;
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'rest_api' );
	}
}
