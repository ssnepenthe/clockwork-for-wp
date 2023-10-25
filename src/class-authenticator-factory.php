<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Authentication\NullAuthenticator;
use Clockwork\Authentication\SimpleAuthenticator;
use InvalidArgumentException;

/**
 * @internal
 */
final class Authenticator_Factory extends Base_Factory {
	public function create_default( Read_Only_Configuration $config ): AuthenticatorInterface {
		$auth = $config->get( 'authentication' );

		if ( ! ( $auth['enabled'] ?? false ) ) {
			return $this->create( 'null' );
		}

		$driver = $auth['driver'] ?? 'simple';

		return $this->create( $driver, $auth['drivers'][ $driver ] ?? [] );
	}

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
