<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Php as Php_Data_Source;

class Php extends Definition {
	public function get_identifier() {
		return 'data_sources.php';
	}

	public function get_subscribed_events() {
		// @todo
		return [];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Php_Data_Source();
		};
	}

	public function is_enabled() {
		// @todo
		return true;
	}
}
