<?php

namespace Clockwork_For_Wp\Definitions\Helpers;

use Pimple\Container;
use Clockwork_For_Wp\Web_Helper;
use Clockwork_For_Wp\Definitions\Definition;

class Web extends Definition {
	public function get_identifier() {
		return 'helpers.web';
	}

	public function get_subscribed_events() {
		return [
			[ 'init',               'register_routes'                   ],
			[ 'template_redirect',  'redirect_shortcut'                 ],
			[ 'redirect_canonical', 'prevent_canonical_redirect', 10, 2 ],
			[ 'template_redirect',  'serve_web_assets'                  ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Web_Helper( $container['routes'] );
		};
	}

	public function is_enabled() {
		$config = $this->plugin->service( 'config' );

		return $config->is_enabled() && $config->is_web_enabled();
	}
}