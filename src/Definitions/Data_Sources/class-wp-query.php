<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface;
use Clockwork_For_Wp\Data_Sources\Wp_Query as Wp_Query_Data_Source;

class Wp_Query extends Definition implements Toggling_Definition_Interface {
	public function get_identifier() {
		return 'data_sources.wp_query';
	}

	public function get_value() {
		return function( Container $container ) {
			$source = new Wp_Query_Data_Source();
			$dep_handler = function() use ( $container, $source ) {
				$source->set_wp_query( $container['wp_query'] );
			};

			if ( did_action( 'init' ) ) {
				$dep_handler();
			} else {
				add_action( 'init', $dep_handler, Plugin::EARLY_EVENT );
			}

			return $source;
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'wp_query' );
	}
}
