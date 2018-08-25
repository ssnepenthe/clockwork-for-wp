<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Xdebug as Xdebug_Data_Source;

class Xdebug extends Definition {
	public function get_identifier() {
		return 'data_sources.xdebug';
	}

	public function get_subscribed_events() {
		return [
			[ 'init', 'on_init' ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Xdebug_Data_Source();
		};
	}

	public function is_enabled() {
		// @todo
		return in_array( 'xdebug', get_loaded_extensions(), true );
	}
}
