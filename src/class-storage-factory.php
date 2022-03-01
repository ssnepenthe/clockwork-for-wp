<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Storage\FileStorage;
use Clockwork\Storage\SqlStorage;
use Clockwork\Storage\StorageInterface;
use InvalidArgumentException;

/**
 * @internal
 */
final class Storage_Factory {
	private $custom_factories = [];

	public function create( string $name, array $config = [] ): StorageInterface {
		if ( $this->has_custom_factory( $name ) ) {
			return $this->call_custom_factory( $name, $config );
		}

		$method = "create_{$name}_storage";

		if ( \method_exists( $this, $method ) ) {
			return ( [ $this, $method ] )( $config );
		}

		throw new InvalidArgumentException( "Unable to create unsupported storage type {$name}" );
	}

	public function register_custom_factory( string $name, callable $factory ) {
		$this->custom_factories[ $name ] = $factory;

		return $this;
	}

	private function call_custom_factory( $name, array $config ): StorageInterface {
		if ( ! $this->has_custom_factory( $name ) ) {
			// @todo is this necessary in final class on private function?
			throw new InvalidArgumentException(
				"No custom factory registered for storage type {$name}"
			);
		}

		return ( $this->custom_factories[ $name ] )( $config );
	}

	private function create_file_storage( array $config ): FileStorage {
		if ( '' === $config['path'] ) {
			throw new InvalidArgumentException( '@todo' );
		}

		return new FileStorage(
			$config['path'],
			$config['dir_permissions'],
			$config['expiration'],
			$config['compress']
		);
	}

	private function create_sql_storage( array $config ): SqlStorage {
		if ( '' === $config['dsn'] ) {
			throw new InvalidArgumentException( '@todo' );
		}

		return new SqlStorage(
			$config['dsn'],
			$config['table'],
			$config['username'],
			$config['password'],
			$config['expiration']
		);
	}

	private function has_custom_factory( $name ): bool {
		return \array_key_exists( $name, $this->custom_factories );
	}
}
