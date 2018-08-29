<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Conditionals extends DataSource {
	protected $conditionals = [];
	protected $default_conditionals = [
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
	];
	protected $special_cases = [];
	protected $default_special_cases = [];

	public function __construct( $conditionals = [], $special_cases = [] ) {
		// @todo Better handling of special cases?
		$this->default_special_cases = [
			'is_main_network' => function() {
				return is_multisite() && is_main_network();
			},
			'is_main_site' => function() {
				return is_multisite() && is_main_site();
			},
		];

		if ( 0 === count( $conditionals ) ) {
			$conditionals = $this->default_conditionals;
		}

		if ( 0 === count( $special_cases ) ) {
			$special_cases = $this->default_special_cases;
		}

		$this->set_conditionals( $conditionals );
		$this->set_special_cases( $special_cases );
	}

	public function add_conditional( callable $conditional ) {
		$this->conditionals[] = $conditional;
	}

	public function add_special_case( $label, \Closure $closure ) {
		$this->special_cases[ $label ] = $closure;
	}

	public function get_conditionals() {
		return $this->conditionals;
	}

	public function get_special_cases() {
		return $this->special_cases;
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'WordPress' );

		$panel->table( 'Conditionals', $this->build_table() );

		return $request;
	}

	public function set_conditionals( array $conditionals ) {
		$this->conditionals = [];

		foreach ( $conditionals as $conditional ) {
			$this->add_conditional( $conditional );
		}
	}

	public function set_special_cases( array $special_cases ) {
		$this->special_cases = [];

		foreach ( $special_cases as $label => $closure ) {
			$this->add_special_case( $label, $closure );
		}
	}

	protected function build_table() {
		$all = array_merge( $this->conditionals, $this->special_cases );

		$table = array_map( function( $label, $callable ) {
			if ( ! is_string( $label ) ) {
				$label = \Clockwork_For_Wp\callable_to_display_string( $callable );
			}

			return [
				'Function' => $label,
				'Value' => true === (bool) call_user_func( $callable ) ? 'true' : 'false',
			];
		}, array_keys( $all ), $all );

		usort( $table, function( $a, $b ) {
			if ( $a['Value'] === $b['Value'] ) {
				return strcmp( $a['Function'], $b['Function'] );
			}

			return 'true' === $a['Value'] ? -1 : 1;
		} );

		return $table;
	}
}
