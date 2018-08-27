<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Cache as Cache_Data_Source;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface as Toggling_Definition;
use Clockwork_For_Wp\Definitions\Subscribing_Definition_Interface as Subscribing_Definition;

class Cache extends Definition implements Subscribing_Definition, Toggling_Definition {
	public function get_identifier() {
		return 'data_sources.cache';
	}

	public function get_subscribed_events() {
		return [
			[ 'setted_transient',      'on_setted_transient',      Plugin::DEFAULT_EVENT, 3 ],
			[ 'setted_site_transient', 'on_setted_site_transient', Plugin::DEFAULT_EVENT, 3 ],
			[ 'deleted_transient',     'on_deleted_transient'                               ],
			[ 'setted_site_transient', 'on_deleted_site_transient'                          ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Cache_Data_Source( $container['wp_object_cache'] );
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'cache' );
	}
}
