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
final class Authenticator_Factory {
	private $custom_factories = [];

	public function create( string $name, array $config = [] ): AuthenticatorInterface {
		if ( $this->has_custom_factory( $name ) ) {
			return $this->call_custom_factory( $name, $config );
		}

		$method = "create_{$name}_authenticator";

		if ( \method_exists( $this, $method ) ) {
			return ( [ $this, $method ] )( $config );
		}

		throw new InvalidArgumentException(
			"Unable to create unsupported authenticator type {$name}"
		);
	}

	public function register_custom_factory( string $name, callable $factory ) {
		$this->custom_factories[ $name ] = $factory;

		return $this;
	}

	private function call_custom_factory( $name, array $config ): AuthenticatorInterface {
		if ( ! $this->has_custom_factory( $name ) ) {
			// @todo is this necessary in final class on private function?
			throw new InvalidArgumentException(
				"No custom factory registered for storage type {$name}"
			);
		}

		return ( $this->custom_factories[ $name ] )( $config );
	}

	private function create_null_authenticator(): NullAuthenticator {
		return new NullAuthenticator();
	}

	private function create_simple_authenticator( array $config ): SimpleAuthenticator {
		if ( ! \array_key_exists( 'password', $config ) ) {
			throw new InvalidArgumentException(
				'Missing "password" key from simple authenticator config array'
			);
		}

		return new SimpleAuthenticator( $config['password'] );
	}

	private function has_custom_factory( $name ): bool {
		return \array_key_exists( $name, $this->custom_factories );
	}
}
