<?php

namespace Clockwork_For_Wp;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Conditionals_Data_Source extends DataSource {
	public function resolve( Request $request ) {
		$request->userData( 'conditionals' )
			->title( 'Conditionals' )
			->table( null, $this->conditionals_table() );

		return $request;
	}

	/**
	 * Adapted from Query Monitor class QM_Collector_Conditionals.
	 */
	protected function conditionals_table() {
		// @todo Sort by value, add counts to the top of panel.
		return array_map( function( $conditional ) {
			return [
				'function' => $conditional,
				'value' => function_exists( $conditional )
					? (bool) call_user_func( $conditional )
					: '(unknown)',
			];
		}, [
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
		] );
	}
}
