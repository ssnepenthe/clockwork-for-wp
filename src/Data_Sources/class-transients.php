<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Transients extends DataSource {
	protected $deleted = [];
	protected $setted = [];

	public function on_setted_transient( $transient, $value, $expiration ) {
		$this->record_setted_transient( $transient, $value, $expiration, 'blog' );
	}

	public function on_setted_site_transient( $transient, $value, $expiration ) {
		$this->record_setted_transient( $transient, $value, $expiration, 'site' );
	}

	public function on_deleted_transient( $transient ) {
		$this->record_deleted_transient( $transient, 'blog' );
	}

	public function on_deleted_site_transient( $transient ) {
		$this->record_deleted_transient( $transient, 'site' );
	}

	protected function record_deleted_transient( $key, $type ) {
		$this->deleted[] = [
			'Key' => $key,
			'Type' => $type,
		];
	}

	protected function record_setted_transient( $key, $value, $expiration, $type ) {
		$this->setted[] = [
			'Key' => $key,
			'Value' => $value,
			'Expiration' => $expiration,
			'Type' => $type,
			'Size' => strlen( maybe_serialize( $value ) ),
		];
	}

	public function resolve( Request $request ) {
		// @todo If object cache data source is disabled, caching panel will be visible even if empty.
		$panel = $request->userData( 'Caching' );

		if ( 0 !== count( $this->setted ) ) {
			$panel->table( 'Setted Transients', $this->setted );
		}

		if ( 0 !== count( $this->deleted ) ) {
			$panel->table( 'Deleted Transients', $this->deleted );
		}

		return $request;
	}
}
