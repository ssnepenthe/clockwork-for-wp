<?php

namespace Clockwork_For_Wp\Definitions;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Config as Plugin_Config;

class Config extends Definition {
	public function get_identifier() {
		return 'config';
	}

	public function get_value() {
		return function( Container $container ) {
			$args = apply_filters( 'cfw_config_args', [] );

			$config = new Plugin_Config( $args );

			do_action( 'cfw_config_init', $config );

			return $config;
		};
	}
}
