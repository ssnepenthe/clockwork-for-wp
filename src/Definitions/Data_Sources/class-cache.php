<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Cache as Cache_Data_Source;

class Cache extends Definition {
	public function get_identifier() {
		return 'data_sources.cache';
	}

	public function get_subscribed_events() {
		return [
			[ 'setted_transient',      'on_setted_transient',      10, 3 ],
			[ 'setted_site_transient', 'on_setted_site_transient', 10, 3 ],
			[ 'deleted_transient',     'on_deleted_transient'            ],
			[ 'setted_site_transient', 'on_deleted_site_transient'       ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Cache_Data_Source( $container['wp_object_cache'] );
		};
	}

	public function is_enabled() {
		return $this->plugin->service( 'config' )->is_collecting_cache_data();
	}
}
