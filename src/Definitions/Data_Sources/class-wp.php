<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Wp as Wp_Data_Source;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface;

class Wp extends Definition implements Toggling_Definition_Interface {
	public function get_identifier() {
		return 'data_sources.wp';
	}

	public function get_value() {
		return function( Container $container ) {
			$source = new Wp_Data_Source();
			$dep_handler = function() use ( $container, $source ) {
				$source->set_wp( $container['wp'] );
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
		return $this->plugin->is_data_source_enabled( 'wp' );
	}
}
