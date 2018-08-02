<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Conditionals extends DataSource {
	protected $conditionals = [];
	protected $matched = [];
	protected $unmatched = [];

	public function __construct( array $conditionals = [] ) {
		if ( 0 === count( $conditionals ) ) {
			$conditionals = [
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
			];
		}

		array_walk( $conditionals, function( $conditional ) {
			$this->add_conditional( $conditional );
		} );
	}

	public function add_conditional( callable $conditional ) {
		$this->conditionals[] = $conditional;
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'conditionals' )->title( 'Conditionals' );

		$panel->counters( [
			'Matched' => count( $this->matched_conditionals_list() ),
			'Unmatched' => count( $this->unmatched_conditionals_list() ),
		] );

		$panel->table( 'Matched', $this->matched_conditionals_table() );
		$panel->table( 'Unmatched', $this->unmatched_conditionals_table() );

		return $request;
	}

	protected function matched_conditionals_list() {
		if ( 0 === count( $this->matched ) ) {
			$this->matched = array_filter( $this->conditionals, function( $conditional ) {
				return (bool) call_user_func( $conditional );
			} );
		}

		return $this->matched;
	}

	protected function matched_conditionals_table() {
		return array_map( function( $conditional ) {
			return [
				'function' => \Clockwork_For_Wp\callable_to_display_string( $conditional ),
				'value' => true,
			];
		}, $this->matched_conditionals_list() );
	}

	protected function unmatched_conditionals_list() {
		if ( 0 === count( $this->unmatched ) ) {
			$this->unmatched = array_filter( $this->conditionals, function( $conditional ) {
				return ! (bool) call_user_func( $conditional );
			} );
		}

		return $this->unmatched;
	}

	protected function unmatched_conditionals_table() {
		return array_map( function( $conditional ) {
			return [
				'function' => \Clockwork_For_Wp\callable_to_display_string( $conditional ),
				'value' => false,
			];
		}, $this->unmatched_conditionals_list() );
	}
}
