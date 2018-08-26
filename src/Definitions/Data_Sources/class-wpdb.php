<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Wpdb as Wpdb_Data_Source;

class Wpdb extends Definition {
	public function get_identifier() {
		return 'data_sources.wpdb';
	}

	public function get_subscribed_events() {
		// @todo
		return [];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Wpdb_Data_Source( $container['wpdb'] );
		};
	}

	public function is_enabled() {
		return defined( 'SAVEQUERIES' )
			&& SAVEQUERIES
			&& $this->plugin->is_data_source_enabled( 'wpdb' );
	}
}
