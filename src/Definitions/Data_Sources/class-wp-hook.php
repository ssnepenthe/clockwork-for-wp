<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Wp_Hook as Wp_Hook_Data_Source;

class Wp_Hook extends Definition {
	public function get_identifier() {
		return 'data_sources.wp_hook';
	}

	public function get_subscribed_events() {
		// @todo
		return [];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Wp_Hook_Data_Source();
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'wp_hook' );
	}
}
