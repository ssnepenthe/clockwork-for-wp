<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\DataSource\DataSource;

class WordPress extends DataSource {
	protected $conditionals;
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

		// @todo Allow user to modify the conditional list?
		$this->conditionals = [
			'list' => [
				'is_404',
				'is_admin',
				'is_archive',
				'is_attachment',
				'is_author',
				'is_blog_admin',
				'is_category',
				'is_comment_feed',
				'is_customize_preview',
				'is_date',
				'is_day',
				'is_embed',
				'is_feed',
				'is_front_page',
				'is_home',
				// @todo Special handling for multisite functions on single site installs?
				'is_main_network',
				'is_main_site',
				'is_month',
				'is_network_admin',
				'is_page',
				'is_page_template',
				'is_paged',
				'is_post_type_archive',
				'is_preview',
				'is_robots',
				'is_rtl',
				'is_search',
				'is_single',
				'is_singular',
				'is_ssl',
				'is_sticky',
				'is_tag',
				'is_tax',
				'is_time',
				'is_trackback',
				'is_user_admin',
				'is_year',
			],
			'table' => [],
		];
	}

	/**
	 * @param  Request $request
	 * @return Request
	 */
	public function resolve( Request $request ) {
		// @todo Consider options for filling the "controller" slot.

		$this->timeline->startEvent( 'total', 'Total execution', 'start' );

		$this->timeline->addEvent(
			'core_timer',
			'Core timer start',
			$this->timestart,
			$this->timestart
		);

		$panel = $request->userData( 'WordPress' );

		$val_counter = function( $value ) {
			return count( array_filter(
				$this->conditionals_table(),
				function( $row ) use ( $value ) {
					return $row['Value'] === $value;
				} )
			);
		};

		$panel->counters( [
			'WP Version' => get_bloginfo( 'version' ),
			'Matched Conditionals' => $val_counter( 'true' ),
			'Unmatched Conditionals' => $val_counter( 'false' ),
		] );

		$request_table = $this->request_table();
		$query_vars_table = $this->query_vars_table();

		if ( 0 !== count( $request_table ) ) {
			$panel->table( 'Request', $request_table );
		}

		if ( 0 !== count( $query_vars_table ) ) {
			$panel->table( 'Query Vars', $query_vars_table );
		}

		$panel->table( 'Constants', $this->constants_table() );
		$panel->table( 'Conditionals', $this->conditionals_table() );

		$request->timelineData = array_merge(
			$request->timelineData,
			$this->timeline->finalize( $request->time )
		);

		return $request;
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

	protected function conditionals_table() {
		if ( 0 === count( $this->conditionals['table'] ) ) {
			$this->conditionals['table'] = array_map( function( $conditional ) {
				return [
					'Function' => \Clockwork_For_Wp\callable_to_display_string( $conditional ),
					'Value' => true === (bool) call_user_func( $conditional ) ? 'true' : 'false',
				];
			}, $this->conditionals['list'] );

			usort( $this->conditionals['table'], function( $a, $b ) {
				if ( $a['Value'] === $b['Value'] ) {
					return strcmp( $a['Function'], $b['Function'] );
				}

				return 'true' === $a['Value'] ? -1 : 1;
			} );
		}

		return $this->conditionals['table'];
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
