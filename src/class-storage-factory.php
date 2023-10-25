<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Storage\FileStorage;
use Clockwork\Storage\SqlStorage;
use InvalidArgumentException;

/**
 * @internal
 */
final class Storage_Factory extends Base_Factory {
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
