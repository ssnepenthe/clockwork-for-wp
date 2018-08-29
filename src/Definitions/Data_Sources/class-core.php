<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface;
use Clockwork_For_Wp\Data_Sources\Core as Core_Data_Source;

class Core extends Definition implements Toggling_Definition_Interface {
	public function get_identifier() {
		return 'data_sources.wordpress';
	}

	public function get_value() {
		return function( Container $container ) {
			return new Core_Data_Source();
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'wordpress' );
	}
}
