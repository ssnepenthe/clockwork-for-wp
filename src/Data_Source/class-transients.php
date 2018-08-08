<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

/**
 * @todo Should this be merged into the cache panel?
 */
class Transients extends DataSource {
	protected $deleted = [];
	protected $setted = [];

	public function resolve( Request $request ) {
		$has_setted = 0 !== count( $this->setted );
		$has_deleted = 0 !== count( $this->deleted );

		if ( $has_deleted || $has_setted ) {
			$panel = $request->userData( 'transients' )->title( 'Transients' );

			if ( $has_deleted ) {
				$panel->table( 'Deleted Transients', $this->deleted );
			}

			if ( $has_setted ) {
				$panel->table( 'Setted Transients', $this->setted );
			}
		}

		return $request;
	}

	public function listen_to_events() {
		add_action( 'setted_transient', function( $transient, $value, $expiration ) {
			$this->set_transient( $transient, $value, $expiration, 'blog' );
		}, 10, 3 );

		add_action( 'setted_site_transient', function( $transient, $value, $expiration ) {
			$this->set_transient( $transient, $value, $expiration, 'site' );
		}, 10, 3 );

		add_action( 'deleted_transient', function( $transient ) {
			$this->delete_transient( $transient, 'blog' );
		} );

		add_action( 'deleted_site_transient', function( $transient ) {
			$this->delete_transient( $transient, 'site' );
		} );
	}

	protected function delete_transient( $key, $type ) {
		$this->deleted[] = [
			'Key' => $key,
			'Type' => $type,
		];
	}

	protected function set_transient( $key, $value, $expiration, $type ) {
		$this->setted[] = [
			'Key' => $key,
			'Value' => $value,
			'Expiration' => $expiration,
			'Type' => $type,
			'Size' => strlen( maybe_serialize( $value ) ),
		];
	}
}
