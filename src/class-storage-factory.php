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

		throw new InvalidArgumentException( '@todo' );
	}

	public function register_custom_factory( string $name, callable $factory ) {
		$this->custom_factories[ $name ] = $factory;

		return $this;
	}

	private function call_custom_factory( $name, array $config ): StorageInterface {
		if ( ! $this->has_custom_factory( $name ) ) {
			// @todo is this necessary in final class on private function?
			throw new InvalidArgumentException( '@todo' );
		}

		return ( $this->custom_factories[ $name ] )( $config );
	}

	private function create_file_storage( array $config ): FileStorage {
		if ( ! \array_key_exists( 'path', $config ) ) {
			throw new InvalidArgumentException(
				'Missing "path" key from file storage config array'
			);
		}

		return new FileStorage(
			$config['path'],
			$config['dir_permissions'] ?? 0700,
			$config['expiration'] ?? null,
			$config['compress'] ?? false
		);
	}

	private function create_sql_storage( array $config ): SqlStorage {
		if ( ! \array_key_exists( 'dsn', $config ) ) {
			throw new InvalidArgumentException( 'Missing "dsn" key from sql storage config array' );
		}

		return new SqlStorage(
			$config['dsn'],
			$config['table'] ?? 'clockwork',
			$config['username'] ?? null,
			$config['password'] ?? null,
			$config['expiration'] ?? null
		);
	}

	private function has_custom_factory( $name ): bool {
		return \array_key_exists( $name, $this->custom_factories );
	}
}
