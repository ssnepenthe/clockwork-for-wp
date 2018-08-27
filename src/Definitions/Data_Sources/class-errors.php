<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Errors as Errors_Data_Source;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface as Toggling_Definition;
use Clockwork_For_Wp\Definitions\Subscribing_Definition_Interface as Subscribing_Definition;

class Errors extends Definition implements Subscribing_Definition, Toggling_Definition {
	public function get_identifier() {
		return 'data_sources.errors';
	}

	public function get_subscribed_events() {
		return [
			[ 'shutdown', 'on_shutdown' ],
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
