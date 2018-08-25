<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\WordPress as WordPress_Data_Source;

class WordPress extends Definition {
	public function get_identifier() {
		return 'data_sources.wordpress';
	}

	public function get_subscribed_events() {
		// @todo
		return [];
	}

	public function get_value() {
		return function( Container $container ) {
			$source = new WordPress_Data_Source( $c['timestart'] );
				$dep_handler = function() use ( $c, $source ) {
					$source->set_wp( $c['wp'] );
					$source->set_wp_query( $c['wp_query'] );
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
		// @todo
		return true;
	}
}
