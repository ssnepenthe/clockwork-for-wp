<?php

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Storage\StorageInterface;

class Api_Helper {
	const EXTENDED_REWRITE_REGEX = '__clockwork\/([0-9-]+|latest)\/extended';
	const EXTENDED_REWRITE_QUERY = 'index.php?cfw_id=$matches[1]&cfw_extended=1';
	const REWRITE_REGEX = '__clockwork\/([0-9-]+|latest)(?:\/(next|previous))?(?(2)\/(\d+))?';
	const REWRITE_QUERY = 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw_count=$matches[3]';

	const ID_QUERY_VAR = 'cfw_id';
	const DIRECTION_QUERY_VAR = 'cfw_direction';
	const COUNT_QUERY_VAR = 'cfw_count';
	const EXTENDED_QUERY_VAR = 'cfw_extended';

	/**
	 * @var StorageInterface
	 */
	protected $storage;
	protected $clockwork;

	/**
	 * @param StorageInterface $storage
	 */
	public function __construct( Clockwork $clockwork, StorageInterface $storage ) {
		$this->clockwork = $clockwork;
		$this->storage = $storage;
	}

	/**
	 * @param  array<string, string> $rules
	 * @return array<string, string>
	 */
	public function register_rewrites( $rules ) {
		// @todo Verify that these should be enabled from config.
		// @todo Verify this filter is working over add_rewrite_rule on init.
		return array_merge( [
			self::EXTENDED_REWRITE_REGEX => self::EXTENDED_REWRITE_QUERY,
			self::REWRITE_REGEX => self::REWRITE_QUERY,
		], $rules );
	}

	/**
	 * @param  array<int, string> $vars
	 * @return array<int, string>
	 */
	public function register_query_vars( $vars ) {
		return array_merge( $vars, [
			self::ID_QUERY_VAR,
			self::DIRECTION_QUERY_VAR,
			self::COUNT_QUERY_VAR,
			self::EXTENDED_QUERY_VAR
		] );
	}

	/**
	 * @return void
	 */
	public function serve_json() {
		// @todo Handle 404s.
		$id = get_query_var( self::ID_QUERY_VAR, null );

		if ( null === $id ) {
			return; // @todo
		}

		$direction = get_query_var( self::DIRECTION_QUERY_VAR, null );
		$count = get_query_var( self::COUNT_QUERY_VAR, null );
		$extended = get_query_var( self::EXTENDED_QUERY_VAR, null );

		if ( 'previous' !== $direction && 'next' !== $direction ) {
			$direction = null;
		}

		if ( null !== $count ) {
			$count = (int) $count;
		}

		if ( null !== $extended ) {
			$extended = true;
		}

		$data = $this->get_data( $id, $direction, $count, $extended );

		wp_send_json( $data ); // @todo
	}

	protected function get_data( $id = null, $direction = null, $count = null, $extended = null ) {
		if ( 'previous' === $direction ) {
			$data = $this->storage->previous( $id, $count );
		} elseif ( 'next' === $direction ) {
			$data = $this->storage->next( $id, $count );
		} elseif ( 'latest' === $id ) {
			$data = $this->storage->latest();
		} else {
			$data = $this->storage->find( $id );
		}

		if ( $extended ) {
			$this->clockwork->extendRequest( $data );
		}

		return $data;
	}
}
