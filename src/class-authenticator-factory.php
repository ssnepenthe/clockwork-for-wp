<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Authentication\NullAuthenticator;
use Clockwork\Authentication\SimpleAuthenticator;
use InvalidArgumentException;

/**
 * @internal
 */
final class Authenticator_Factory extends Base_Factory {
	protected function create_null_instance(): NullAuthenticator {
		return new NullAuthenticator();
	}

	protected function create_simple_instance( array $config ): SimpleAuthenticator {
		if ( ! \array_key_exists( 'password', $config ) ) {
			throw new InvalidArgumentException(
				'Missing "password" key from simple authenticator config array'
			);
		}

		return new SimpleAuthenticator( $config['password'] );
	}
}
