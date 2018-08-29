<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Constants extends DataSource {
	protected $constants = [];
	protected $default_constants = [
		'WP_DEBUG',
		'WP_DEBUG_DISPLAY',
		'WP_DEBUG_LOG',
		'SCRIPT_DEBUG',
		'WP_CACHE',
		'CONCATENATE_SCRIPTS',
		'COMPRESS_SCRIPTS',
		'COMPRESS_CSS',
		'WP_LOCAL_DEV',
	];

	public function __construct( array $constants = [] ) {
		if ( is_multisite() ) {
			$this->default_constants[] = 'SUNRISE';
		}

		if ( 0 === count( $constants ) ) {
			$constants = $this->default_constants;
		}

		$this->set_constants( $constants );
	}

	public function add_constant( $constant ) {
		if ( ! is_string( $constant ) ) {
			throw new \InvalidArgumentException( '@todo' );
		}

		$this->constants[] = $constant;
	}

	public function get_constants() {
		return $this->constants;
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'WordPress' );

		$panel->table( 'Constants', $this->build_table() );

		return $request;
	}

	public function set_constants( array $constants ) {
		$this->constants = [];

		foreach ( $constants as $constant ) {
			$this->add_constant( $constant );
		}
	}

	protected function build_table() {
		return array_map( function( $constant ) {
			$value = 'undefined';

			// @todo Should constants be limited to bool?
			if ( defined( $constant ) ) {
				$value = filter_var( constant( $constant ), FILTER_VALIDATE_BOOLEAN )
					? 'true'
					: 'false';
			}

			return [
				'Name' => $constant,
				'Value' => $value,
			];
		}, $this->constants );
	}
}
