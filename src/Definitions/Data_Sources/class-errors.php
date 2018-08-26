<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Errors as Errors_Data_Source;

class Errors extends Definition {
	public function get_identifier() {
		return 'data_sources.errors';
	}

	public function get_subscribed_events() {
		return [
			[ 'shutdown', 'print_recorded_errors' ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Errors_Data_Source();
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'errors' );
	}
}
