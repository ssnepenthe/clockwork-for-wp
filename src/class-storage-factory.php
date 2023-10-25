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
final class Storage_Factory extends Base_Factory {
	public function create_default( Read_Only_Configuration $config ): StorageInterface {
		$storage_config = $config->get( 'storage' );
		$driver = $storage_config['driver'];
		$driver_config = $storage_config['drivers'][ $driver ] ?? [];

		if ( null === ( $driver_config['expiration'] ?? null ) && null !== $storage_config['expiration'] ) {
			$driver_config['expiration'] = $storage_config['expiration'];
		}

		return $this->create( $driver, $driver_config );
	}

	protected function create_file_instance( array $config ): FileStorage {
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

	protected function create_sql_instance( array $config ): SqlStorage {
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
}
