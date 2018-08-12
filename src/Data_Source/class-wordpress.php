<?php

namespace Clockwork_For_Wp\Data_Source;

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

		$panel = $request->userData( 'wordpress' )->title( 'WordPress' );

		$panel->counters( [ 'WP Version' => get_bloginfo( 'version' ) ] );

		$request_table = $this->request_table();
		$query_vars_table = $this->query_vars_table();

		if ( 0 !== count( $request_table ) ) {
			$panel->table( 'Request', $request_table );
		}

		if ( 0 !== count( $query_vars_table ) ) {
			$panel->table( 'Query Vars', $query_vars_table );
		}

		$panel->table( 'Constants', $this->constants_table() );

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
		$this->wp = is_object( $wp ) ? $wp : null;
	}

	public function set_wp_query( $wp_query ) {
		$this->wp_query = is_object( $wp_query ) ? $wp_query : null;
	}

	protected function constants_table() {
		return array_map(
			function( $constant ) {
				$value = 'undefined';

				if ( defined( $constant ) ) {
					$value = filter_var( constant( $constant ), FILTER_VALIDATE_BOOLEAN )
						? 'true'
						: 'false';
				}

				return [
					'Name' => $constant,
					'Value' => $value,
				];
			},
			// @todo Filterable list? Should they be limited to bool? List currently matches the one
			// from the environment tab in the Query Monitor plugin.
			array_filter( [
				'WP_DEBUG',
				'WP_DEBUG_DISPLAY',
				'WP_DEBUG_LOG',
				'SCRIPT_DEBUG',
				'WP_CACHE',
				'CONCATENATE_SCRIPTS',
				'COMPRESS_SCRIPTS',
				'COMPRESS_CSS',
				'WP_LOCAL_DEV',
				is_multisite() ? 'SUNRISE' : '',
			] )
		);
	}

	/**
	 * Adapted from Query Monitor QM_Collector_Request class.
	 */
	protected function query_vars() {
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

	protected function query_vars_table() {
		$query_vars = $this->query_vars();

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

	protected function request_table() {
		if ( null === $this->wp ) {
			return [];
		}

		return array_map(
			function( $var ) {
				return [
					'Variable' => $var,
					'Value' => $this->wp->{$var},
				];
			},
			array_filter(
				[ 'request', 'query_string', 'matched_rule', 'matched_query' ],
				function( $var ) {
					return property_exists( $this->wp, $var ) && $this->wp->{$var};
				}
			)
		);
	}
}
