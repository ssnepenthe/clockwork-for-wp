<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface;
use Clockwork_For_Wp\Data_Sources\Wp_Rewrite as Wp_Rewrite_Data_Source;

class Wp_Rewrite extends Definition implements Toggling_Definition_Interface {
	public function get_identifier() {
		return 'data_sources.wp_rewrite';
	}

	public function get_value() {
		return function( Container $container ) {
			$source = new Wp_Rewrite_Data_Source();
			$dep_handler = function() use ( $container, $source ) {
				$source->set_wp_rewrite( $container['wp_rewrite'] );
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
		return $this->plugin->is_data_source_enabled( 'wp_rewrite' );
	}
}
