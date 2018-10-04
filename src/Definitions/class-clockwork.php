<?php

namespace Clockwork_For_Wp\Definitions;

use Pimple\Container;
use Clockwork\Clockwork as Clockwork_Core;
use Clockwork_For_Wp\Definitions\Definition;

class Clockwork extends Definition {
	public function get_identifier() {
		return 'clockwork';
	}

	public function get_value() {
		return function( Container $container ) {
			$clockwork = new Clockwork_Core();

			$enabled_definitions = array_filter(
				$this->plugin->definitions(),
				function( $definition ) {
					if ( 0 !== strpos( $definition->get_identifier(), 'data_sources.' ) ) {
						return false;
					}

					return $definition instanceof Toggling_Definition_Interface
						? $definition->is_enabled()
						: true;
				}
			);

			foreach ( $enabled_definitions as $definition ) {
				$clockwork->addDataSource( $container[ $definition->get_identifier() ] );
			}

			$clockwork->setAuthenticator( $container['clockwork.authenticator'] );
			$clockwork->setStorage( $container['clockwork.storage'] );

			return $clockwork;
		};
	}
}
