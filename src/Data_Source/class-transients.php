<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

class Transients extends DataSource implements Subscriber {
	protected $setted = [];
	protected $deleted = [];

	public function subscribe_to_events( Event_Manager $event_manager ) : void {
		$event_manager
			->on( 'setted_transient', function( $transient, $value, $expiration ) {
				$this->setted( $transient, $value, $expiration );
			} )
			->on( 'setted_site_transient', function( $transient, $value, $expiration ) {
				$this->setted( $transient, $value, $expiration, $is_site = true );
			} )
			->on( 'deleted_transient', function( $transient ) {
				$this->deleted( $transient );
			} )
			->on( 'deleted_site_transient', function( $transient ) {
				$this->deleted( $transient, $is_site = true );
			} );
	}

	public function resolve( Request $request ) {
		if ( count( $this->setted ) > 0 ) {
			$request->userData( 'Caching' )->table( 'Setted Transients', $this->setted );
		}

		if ( count( $this->deleted ) > 0 ) {
			$request->userData( 'Caching' )->table( 'Deleted Transients', $this->deleted );
		}

		return $request;
	}

	public function setted( $key, $value = null, $expiration = null, $is_site = false ) {
		$this->setted[] = $this->prepare( 'setted', $key, $value, $expiration, $is_site );

		return $this;
	}

	public function deleted( $key, $is_site = false ) {
		$this->deleted[] = $this->prepare( 'deleted', $key, null, null, $is_site );

		return $this;
	}

	// @todo External helper function?
	protected function prepare( $type, $key, $value = null, $expiration = null, $is_site = false ) {
		if ( ! in_array( $type, [ 'setted', 'deleted' ], true ) ) {
			throw new \InvalidArgumentException( '@todo' );
		}

		$for_size = $value;

		if ( null !== $for_size && ! is_string( $for_size ) ) {
			$for_size = serialize( $for_size );
		}

		return array_filter( [
			'Type' => $type,
			'Key' => $key,
			'Value' => $value,
			'Expiration' => $expiration,
			'Is Site' => $is_site ? 'Yes' : 'No',
			'Size' => is_string( $for_size ) ? strlen( $for_size ) : null,
		], function( $value ) {
			return null !== $value;
		} );
	}
}
