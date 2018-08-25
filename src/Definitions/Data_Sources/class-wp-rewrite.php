<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Wp_Rewrite as Wp_Rewrite_Data_Source;

class Wp_Rewrite extends Definition {
	public function get_identifier() {
		return 'data_sources.wp_rewrite';
	}

	public function get_subscribed_events() {
		// @todo
		return [];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Wp_Rewrite_Data_Source( $container['wp_rewrite'] );
		};
	}

	public function is_enabled() {
		return $this->plugin->service( 'config' )->is_collecting_rewrite_data();
	}
}