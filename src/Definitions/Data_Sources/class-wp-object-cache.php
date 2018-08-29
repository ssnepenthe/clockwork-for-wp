<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface;
use Clockwork_For_Wp\Data_Sources\Wp_Object_Cache as Wp_Object_Cache_Data_Source;

class Wp_Object_Cache extends Definition implements Toggling_Definition_Interface {
	public function get_identifier() {
		return 'data_sources.object_cache';
	}

	public function get_value() {
		return function( Container $container ) {
			$source = new Wp_Object_Cache_Data_Source();
			$dep_handler = function() use ( $container, $source ) {
				$source->set_wp_object_cache( $container['wp_object_cache'] );
			};

			// Cache global should always be available by the time plugins load but to be safe...
			if ( did_action( 'init' ) ) {
				$dep_handler();
			} else {
				add_action( 'init', $dep_handler );
			}

			return $source;
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'object_cache' );
	}
}
