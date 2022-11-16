<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Request\Request;
use Clockwork\Storage\Search;
use Clockwork\Storage\StorageInterface;

final class Metadata {
	private $storage;

	private $support;

	public function __construct( Clockwork_Support $support, StorageInterface $storage ) {
		$this->storage = $storage;
		$this->support = $support;
	}

	public function get( $id = null, $direction = null, $count = null ) {
		[ $direction, $count ] = $this->apply_defaults( $direction, $count );

		if ( 'previous' === $direction ) {
			$data = $this->storage->previous( $id, $count, Search::fromRequest( $_GET ) );
		} elseif ( 'next' === $direction ) {
			$data = $this->storage->next( $id, $count, Search::fromRequest( $_GET ) );
		} elseif ( 'latest' === $id ) {
			$data = $this->storage->latest( Search::fromRequest( $_GET ) );
		} else {
			$data = $this->storage->find( $id );
		}

		return $data;
	}

	public function get_extended( $id = null, $direction = null, $count = null ) {
		$data = $this->get( $id, $direction, $count );

		$this->support->extend_request( $data );

		return $data;
	}

	public function update( Request $request ) {
		/**
		 * @psalm-suppress UndefinedInterfaceMethod
		 *
		 * @see https://github.com/itsgoingd/clockwork/pull/522
		 */
		return $this->storage->update( $request );
	}

	private function apply_defaults( $direction, $count ) {
		return [
			\in_array( $direction, [ 'previous', 'next' ], true ) ? $direction : null,
			null !== $count ? (int) $count : null,
		];
	}
}
