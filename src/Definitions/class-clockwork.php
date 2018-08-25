<?php

namespace Clockwork_For_Wp\Definitions;

use Pimple\Container;
use Clockwork\Clockwork as Clockwork_Core;
use Clockwork_For_Wp\Definitions\Definition;

class Clockwork extends Definition {
	public function get_identifier() {
		return 'clockwork';
	}

	public function get_subscribed_events() {
		// @todo
		return [];
	}

	public function get_value() {
		return function( Container $container ) {
			$clockwork = new Clockwork_Core();

			$enabled_definitions = array_filter(
				$this->plugin->definitions(),
				function( $definition ) {
					return 0 === strpos( $definition->get_identifier(), 'data_sources.' )
						&& $definition->is_enabled();
				}
			);

			foreach ( $enabled_definitions as $definition ) {
				$clockwork->addDataSource( $container[ $definition->get_identifier() ] );
			}

			$clockwork->setStorage( $container['clockwork.storage'] );

			return $clockwork;
		};
	}

	public function is_enabled() {
		// @todo
		return true;
	}
}
