<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Wpdb as Wpdb_Data_Source;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface;

class Wpdb extends Definition implements Toggling_Definition_Interface {
	public function get_identifier() {
		return 'data_sources.wpdb';
	}

	public function get_value() {
		return function( Container $container ) {
			$source = new Wpdb_Data_Source();
			$dep_handler = function() use ( $container, $source ) {
				$source->set_wpdb( $container['wpdb'] );
			};

			if ( did_action( 'init' ) ) {
				$dep_handler();
			} else {
				add_action( 'init', $dep_handler );
			}

			return $source;
		};
	}

	public function is_enabled() {
		return defined( 'SAVEQUERIES' )
			&& SAVEQUERIES
			&& $this->plugin->is_data_source_enabled( 'wpdb' );
	}
}
