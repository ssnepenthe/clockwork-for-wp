<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp_Query extends DataSource {
	protected $wp_query;

	public function __construct( $wp_query = null ) {
		$this->set_wp_query( $wp_query );
	}

	public function resolve( Request $request ) {
		$table = $this->build_table();

		if ( 0 !== count( $table ) ) {
			$panel = $request->userData( 'WordPress' );

			$panel->table( 'Query Vars', $table );
		}

		return $request;
	}

	public function set_wp_query( $wp_query ) {
		$this->wp_query = is_object( $wp_query ) ? $wp_query : null;
	}

	/**
	 * Adapted from Query Monitor QM_Collector_Request class.
	 */
	protected function all_vars() {
		if ( null === $this->wp_query || ! property_exists( $this->wp_query, 'query_vars' ) ) {
			return [];
		}

		$plugin_vars = apply_filters( 'query_vars', [] );

		$query_vars = array_filter(
			$this->wp_query->query_vars,
			function( $value, $key ) use ( $plugin_vars ) {
				return ( isset( $plugin_vars[ $key ] ) && '' !== $value ) || ! empty( $value );
			},
			ARRAY_FILTER_USE_BOTH
		);

		ksort( $query_vars );

		return $query_vars;
	}

	protected function build_table() {
		$query_vars = $this->all_vars();

		return array_map(
			function( $value, $key ) {
				return [
					'Variable' => $key,
					'Value' => $value,
				];
			},
			$query_vars,
			array_keys( $query_vars )
		);
	}
}
