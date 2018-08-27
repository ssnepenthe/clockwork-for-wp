<?php

namespace Clockwork_For_Wp\Definitions;

use Pimple\Container;
use Clockwork\Storage\FileStorage;
use Clockwork_For_Wp\Definitions\Definition;

class Clockwork_Storage extends Definition {
	public function get_identifier() {
		return 'clockwork.storage';
	}

	public function get_value() {
		return function( Container $container ) {
			$storage = new FileStorage(
				$container['config']->get_storage_files_path(),
				0700,
				$container['config']->get_storage_expiration()
			);

			$storage->filter = $container['config']->get_storage_filter();

			return $storage;
		};
	}
}
