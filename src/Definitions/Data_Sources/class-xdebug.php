<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Xdebug as Xdebug_Data_Source;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface as Toggling_Definition;
use Clockwork_For_Wp\Definitions\Subscribing_Definition_Interface as Subscribing_Definition;

class Xdebug extends Definition implements Subscribing_Definition, Toggling_Definition {
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
		return extension_loaded( 'xdebug' ) && $this->plugin->is_data_source_enabled( 'xdebug' );
	}
}
