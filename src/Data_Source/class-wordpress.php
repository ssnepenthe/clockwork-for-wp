<?php

namespace Clockwork_For_Wp\Data_Source;

use WP;
use WP_Query;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\DataSource\DataSource;

class WordPress extends DataSource {
	/**
	 * @var Timeline
	 */
	protected $timeline;
	protected $timestart;
	protected $wp;
	protected $wp_query;

	/**
	 * @param Timeline|null $timeline
	 */
	public function __construct(
		$timestart,
		$wp = null,
		$wp_query = null,
		Timeline $timeline = null
	) {
		$this->set_timeline( $timeline ?: new Timeline() );
		$this->set_timestart( $timestart );
		$this->set_wp( $wp );
		$this->set_wp_query( $wp_query );
	}

	/**
	 * @param  Request $request
	 * @return Request
	 */
	public function resolve( Request $request ) {
		// @todo Consider options for filling the "controller" slot.
		// @todo Consider configuring a custom error handler to save errors in the "log" slot.

		$request_table = $this->request_table();
		$has_request_data = 0 !== count( $request_table );
		$query_vars_table = $this->query_vars_table();
		$has_query_vars_data = 0 !== count( $query_vars_table );

		if ( $has_request_data || $has_query_vars_data ) {
			$panel = $request->userData( 'wordpress' )->title( 'WordPress' );

			if ( $has_request_data ) {
				$panel->table( 'Request', $request_table );
			}

			if ( $has_query_vars_data ) {
				$panel->table( 'Query Vars', $query_vars_table );
			}
		}

		$request->timelineData = array_merge(
			$request->timelineData,
			$this->timeline->finalize( $request->time )
		);

		return $request;
	}

	/**
	 * @return void
	 */
	public function listen_to_events() {
		$this->timeline->startEvent( 'total', 'Total execution', 'start' );

		$this->timeline->addEvent(
			'core_timer',
			'Core timer start',
			$this->timestart,
			$this->timestart
		);
	}

	public function set_timeline( Timeline $timeline ) {
		$this->timeline = $timeline;
	}

	public function set_timestart( $timestart ) {
		$this->timestart = is_float( $timestart ) ? $timestart : null;
	}

	public function set_wp( $wp ) {
		$this->wp = $wp instanceof WP ? $wp : null;
	}

	public function set_wp_query( $wp_query ) {
		$this->wp_query = $wp_query instanceof WP_Query ? $wp_query : null;
	}

	/**
	 * Adapted from Query Monitor QM_Collector_Request class.
	 */
	protected function query_vars() {
		if ( null === $this->wp_query ) {
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

	protected function query_vars_table() {
		$query_vars = $this->query_vars();

		return array_map( function( $value, $key ) {
			return [
				'Variable' => $key,
				'Value' => $value,
			];
		}, $query_vars, array_keys( $query_vars ) );
	}

	protected function request_table() {
		if ( null === $this->wp ) {
			return [];
		}

		$table = array_map( function( $var ) {
			return [
				'Variable' => $var,
				'Value' => $this->wp->{$var} ? $this->wp->{$var} : false,
			];
		}, [ 'request', 'query_string', 'matched_rule', 'matched_query' ] );

		return array_filter( $table, function( $row ) {
			return false !== $row['Value'];
		} );
	}
}
