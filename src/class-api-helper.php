<?php

namespace Clockwork_For_Wp;

use Clockwork\Storage\StorageInterface;

class Api_Helper {
	const REWRITE_REGEX = '__clockwork\/([0-9-]+|app|latest)(?:\/(next|previous))?(?(2)\/(\d+))?';
	const REWRITE_QUERY = 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw_count=$matches[3]';

	const ID_QUERY_VAR = 'cfw_id';
	const DIRECTION_QUERY_VAR = 'cfw_direction';
	const COUNT_QUERY_VAR = 'cfw_count';

	protected $storage;

	public function __construct( StorageInterface $storage ) {
		$this->storage = $storage;
	}

	public function register_rewrites( $rules ) {
		// @todo Verify that these should be enabled from config.
		// @todo Verify this filter is working over add_rewrite_rule on init.
		return array_merge( [ self::REWRITE_REGEX => self::REWRITE_QUERY ], $rules );
	}

	public function register_query_vars( $vars ) {
		return array_merge( $vars, [
			self::ID_QUERY_VAR,
			self::DIRECTION_QUERY_VAR,
			self::COUNT_QUERY_VAR,
		] );
	}

	public function serve_json() {
		$id = get_query_var( self::ID_QUERY_VAR );

		if ( ! $id ) {
			return;
		}

		$data = $this->storage->find( $id );

		// @todo Verify data is array/not null?
		// @todo Handle direction and count vars.

		wp_send_json( $data );
	}
}
