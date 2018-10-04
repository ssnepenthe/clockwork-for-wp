<?php

namespace Clockwork_For_Wp\Definitions;

use Pimple\Container;
use Clockwork\Authentication\NullAuthenticator;
use Clockwork\Authentication\SimpleAuthenticator;

class Clockwork_Authenticator extends Definition {
	public function get_identifier() {
		return 'clockwork.authenticator';
	}

	public function get_value() {
		return function( Container $container ) {
			$config = $container['config'];

			if ( $config->is_authentication_required() ) {
				return new SimpleAuthenticator( $config->get_authentication_password() );
			}

			return new NullAuthenticator();
		};
	}
}
