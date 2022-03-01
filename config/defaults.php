<?php

declare(strict_types=1);

return [

	'requests' => [
		'sensitive_patterns' => [
			'/' . \implode( '|', [ AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE ] ) . '/i',
			'/pass|pwd/i',
		],
	],

	'data_sources' => [
		'conditionals' => [
			'config' => [
				'conditionals' => [
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
					[
						'conditional' => static function (): bool {
							return \get_post() instanceof WP_Post && \is_sticky();
						},
						'label' => 'is_sticky()',
					],
					'is_tag',
					'is_tax',
					'is_time',
					'is_trackback',
					'is_user_admin',
					'is_year',
					[ 'conditional' => 'is_main_network', 'when' => 'is_multisite' ],
					[ 'conditional' => 'is_main_site', 'when' => 'is_multisite' ],
				],
			],
		],

		'constants' => [
			'config' => [
				'constants' => [
					'WP_DEBUG',
					'WP_DEBUG_DISPLAY',
					'WP_DEBUG_LOG',
					'SCRIPT_DEBUG',
					'WP_CACHE',
					'CONCATENATE_SCRIPTS',
					'COMPRESS_SCRIPTS',
					'COMPRESS_CSS',
					'WP_LOCAL_DEV',
					[
						'constant' => 'SUNRISE',
						'when' => 'is_multisite',
					],
				],
			],
		],
	],

	'storage' => [
		'drivers' => [
			'file' => [
				'path' => WP_CONTENT_DIR . '/cfw-data',
			],
		],
	],

];
