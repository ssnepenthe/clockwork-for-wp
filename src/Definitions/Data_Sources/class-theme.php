<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Theme as Theme_Data_Source;

class Theme extends Definition {
	public function get_identifier() {
		return 'data_sources.theme';
	}

	public function get_subscribed_events() {
		return [
			[ 'body_class',       'on_body_class',       Plugin::LATE_EVENT ],
			[ 'template_include', 'on_template_include', Plugin::LATE_EVENT ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			$source = new Theme_Data_Source();
			$dep_handler = function() use ( $container, $source ) {
				$source->set_content_width( $container['content_width'] );
			};

			if ( did_action( 'init' ) ) {
				$dep_handler();
			} else {
				add_action( 'init', $dep_handler, Plugin::EARLY_EVENT );
			}

			return $source;
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'theme' );
	}
}
