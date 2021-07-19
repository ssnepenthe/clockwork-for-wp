<?php

use Clockwork\Authentication\SimpleAuthenticator;
use Clockwork\Storage\FileStorage;
use Clockwork\Storage\SqlStorage;
use Clockwork_For_Wp\Data_Source\Conditionals;
use Clockwork_For_Wp\Data_Source\Constants;
use Clockwork_For_Wp\Data_Source\Core;
use Clockwork_For_Wp\Data_Source\Errors;
use Clockwork_For_Wp\Data_Source\Rest_Api;
use Clockwork_For_Wp\Data_Source\Theme;
use Clockwork_For_Wp\Data_Source\Timestart;
use Clockwork_For_Wp\Data_Source\Transients;
use Clockwork_For_Wp\Data_Source\Wp;
use Clockwork_For_Wp\Data_Source\Wp_Hook;
use Clockwork_For_Wp\Data_Source\Wp_Http;
use Clockwork_For_Wp\Data_Source\Wp_Mail;
use Clockwork_For_Wp\Data_Source\Wp_Object_Cache;
use Clockwork_For_Wp\Data_Source\Wp_Query;
use Clockwork_For_Wp\Data_Source\Wp_Rewrite;
use Clockwork_For_Wp\Data_Source\Wpdb;
use Clockwork_For_Wp\Data_Source\Xdebug;

return [

	'enable' => true,

	'collect_data_always' => false,

	'web' => true,

	'register_helpers' => true,

	'headers' => [
		// 'Accept' => 'application/vnd.com.whatever.v1+json',
	],

	'requests' => [
		'on_demand' => false,

		'errors_only' => false,

		// @todo Determine a good number to use for slow WordPress requests.
		'slow_threshold' => 1000,

		'slow_only' => false,

		'sample' => false,

		'except' => [],

		'only' => [],

		'except_preflight' => true,
	],

	'data_sources' => [
		'conditionals' => [
			'enabled' => false,
			'data_source_class' => Conditionals::class,
		],
		'constants' => [
			'enabled' => false,
			'data_source_class' => Constants::class,
		],
		'core' => [
			'enabled' => false,
			'data_source_class' => Core::class,
		],
		'errors' => [
			'enabled' => false,
			'data_source_class' => Errors::class,
		],
		'rest_api' => [
			'enabled' => false,
			'data_source_class' => Rest_Api::class,
		],
		'theme' => [
			'enabled' => false,
			'data_source_class' => Theme::class,
		],
		'transients' => [
			'enabled' => false,
			'data_source_class' => Transients::class,
		],
		'wp_hook' => [
			'enabled' => false,
			'data_source_class' => Wp_Hook::class,
		],
		'wp_http' => [
			'enabled' => false,
			'data_source_class' => Wp_Http::class,
		],
		'wp_mail' => [
			'enabled' => false,
			'data_source_class' => Wp_Mail::class,
		],
		'wp_object_cache' => [
			'enabled' => false,
			'data_source_class' => Wp_Object_Cache::class,
		],
		'wp_query' => [
			'enabled' => false,
			'data_source_class' => Wp_Query::class,
		],
		'wp_rewrite' => [
			'enabled' => false,
			'data_source_class' => Wp_Rewrite::class,
		],
		'wp' => [
			'enabled' => false,
			'data_source_class' => Wp::class,
		],
		'wpdb' => [
			'enabled' => false,
			'data_source_class' => Wpdb::class,
		],
		'xdebug' => [
			'enabled' => false,
			'data_source_class' => Xdebug::class,
		],
	],

	'storage' => [
		'driver' => 'file',

		'drivers' => [
			'file' => [
				'class' => FileStorage::class,
				'config' => [
					'dir_permissions' => 0700,
					'expiration' => 60 * 24 * 7,
					'path' => WP_CONTENT_DIR . '/cfw-data',
				],
			],

			'sql' => [
				'class' => SqlStorage::class,
				'config' => [
					'dsn' => '',
					'table' => 'clockwork',
					'username' => null,
					'password' => null,
					'expiration' => 60 * 24 * 7,
				],
			],
		],
	],

	'authentication' => [
		'enabled' => false,

		'driver' => 'simple',

		'drivers' => [
			'simple' => [
				'class' => SimpleAuthenticator::class,
				'config' => [
					'password' => 'VerySecretPassword',
				],
			],
		],
	],

	'serialization' => [
		'depth' => 10,
		'blackbox' => [
			Pimple\Container::class,
			Pimple\Psr11\Container::class,
		],
	],

	'stack_traces' => [
		'enabled' => true,
		'skip_vendors' => [],
		'skip_namespaces' => [],
		'skip_classes' => [],
		'limit' => 10,
	],

];
