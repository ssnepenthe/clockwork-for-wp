<?php

declare(strict_types=1);

return [

	// bool. Enable or disable clockwork.
	'enable' => true,

	// bool. Collect data even when clockwork is disabled.
	'collect_data_always' => false,

	// bool. Enable or disable collection of client metrics.
	'collect_client_metrics' => true,

	// bool. Enable or disable collection of admin-ajax "heartbeat" requests.
	'collect_heartbeat' => true,

	// bool. Enable or disable the the web UI at http://yoursite.test/__clockwork/app.
	'web' => true,

	// bool. Enable or disable the Clockwork browser toolbar.
	'toolbar' => true,

	// bool. Load the clock() global function.
	'register_helpers' => true,

	// array<string, string>. A list of additional headers to send with responses. Header names are prefixed with "X-Clockwork-Header-".
	'headers' => [],

	// Configure HTTP requests data collection.
	'requests' => [

		// bool|string. Only collect data when clockwork extension is open or a request includes a "clockwork-profile" cookie or get/post data key. If set to a string this will be used as a "secret" that must be passed as the "clockwork-profile" value.
		'on_demand' => false,

		// bool|int. Sample the collected requests (e.g. set to 100 to only collect 1 in 100 requests).
		'sample' => false,

		// string[]. List of patterns indicating URIs for which data should not be collected.
		'except' => [],

		// string[]. List of patterns indicating URIs for which data should be collected.
		'only' => [],

		// bool. True prevents data collection on OPTIONS requests.
		'except_preflight' => true,

		// string[]. List of patterns indicating sensitive data that should be redacted from request input (get, post, cookies, session, etc.).
		'sensitive_patterns' => [

			'/' . \implode( '|', [ AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE ] ) . '/i',
			'/pass|pwd/i',

		],

	],

	// Configure data sources. Enable or disable any data source by setting the corresponding "enabled" value.
	'data_sources' => [

		// The conditionals data source records the values of a number of WordPress conditional functions.
		'conditionals' => [

			'enabled' => false,

			'config' => [

				// array<int, array{conditional: callable, label?: string, when?: callable}|callable>. List of conditionals to check. Can optionally be provided as an array with required key 'conditional' and optional keys 'label' and 'when'. When provided as array, 'conditional' refers to the conditional to check, 'label' allows you to override the label displayed in Clockwork and 'when' is a callable that allows check for this conditional to be disabled at runtime.
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

		// The constants data source records the values of a number of WordPress defined constants.
		'constants' => [

			'enabled' => false,

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

		// The core data source records basic WordPress info such as the current version and total execution time.
		'core' => [

			'enabled' => false,

		],

		// The errors data source records PHP errors to the clockwork log.
		'errors' => [

			'enabled' => false,

			'config' => [

				// int|false. Bitmask indicating error types that should not be recorded. False to disable.
				'except_types' => false,

				// int|false. Bitmask indicating error types that should be recorded. False to disable.
				'only_types' => false,

				// string[]. A list of message patterns indicating an error should not be recorded.
				'except_messages' => [],

				// string[]. A list of message patterns indicating an error should be recorded.
				'only_messages' => [],

				// string[]. A list of file patterns indicating an error should not be recorded.
				'except_files' => [],

				// string[]. A list of file patterns indicating an error should be recorded.
				'only_files' => [],

				// bool. Enable or disable collection of @-suppressed errors.
				'include_suppressed_errors' => false,

			],

		],

		// The rest api data source records data about all registered rest routes.
		'rest_api' => [

			'enabled' => false,

		],

		// The theme data source records theme-specific data such as body classes and loaded template parts.
		'theme' => [

			'enabled' => false,

		],

		// The transients data source records all setted and deleted transients in a given request.
		'transients' => [

			'enabled' => false,

		],

		// The wp_hook data source records data about all registered hooks.
		'wp_hook' => [

			'enabled' => false,

			'config' => [

				// string[]. A list of patterns indicating hook names that shouldn't be recorded.
				'except_tags' => [],

				// string[]. A list of patterns indicating hook names that should be recorded. Takes precedence over "except_tags".
				'only_tags' => [],

				// string[]. A list of patterns indicating callback names that shouldn't be recorded.
				'except_callbacks' => [ 'clockwork-for-wp' ],

				// string[]. A list of patterns indicating callback names that should be recorded. Takes precedence over "except_callbacks".
				'only_callbacks' => [],

				// bool. When set to true it will record data about all registered hooks instead of just the ones triggered for the current request.
				'all_hooks' => false,

			],

		],

		// The wp_http data source records data about HTTP requests made with the WP HTTP API to the clockwork log and timeline.
		'wp_http' => [

			'enabled' => false,

		],

		// The wp_mail data source records mail sent with wp_mail() as well as any errors encountered when sending mail.
		'wp_mail' => [

			'enabled' => false,

		],

		// The wp_object_cache data source records data about object cache interaction on a given request.
		'wp_object_cache' => [

			'enabled' => false,

		],

		// The wp_query data source records the values of all query vars for a given request.
		'wp_query' => [

			'enabled' => false,

		],

		// The wp_redirect data source adds some redirect context to the clockwork log.
		'wp_redirect' => [

			'enabled' => false,

		],

		// The wp_rewrite data source records all rewrite rules currently registered with WordPress.
		'wp_rewrite' => [

			'enabled' => false,

		],

		// The wp data sources records some basic data from the global wp instance.
		'wp' => [

			'enabled' => false,

		],

		// The wpdb data source records all database queries made in the current request and can attempt to identify duplicate queries. To enable you must also defined the "SAVEQUERIES" constant to true.
		'wpdb' => [

			'enabled' => false,

			'config' => [

				// bool. When true it will add duplicate queries with counts to the clockwork log.
				'detect_duplicate_queries' => false,

				// bool. When true only database queries that take longer than "slow_threshold" below are collected.
				'slow_only' => false,

				// int. Time in milliseconds after which a query will be marked as slow. Disregarded unless "slow_only" is true.
				'slow_threshold' => 50, // Default query monitor value... Is this reasonable?

				// array<string, string>. A map of regular expressions to "models". Used to assign a model to a query in the database tab. If you need more than a simple regular expression to determine a query model, use the "cfw_data_sources_wpdb_init" hook.
				'pattern_model_map' => [
					// @todo Should we include "old tables"?
					'/blog(?:_version)?s$/' => 'BLOG',
					'/comment(?:s|meta)$/' => 'COMMENT',
					'/links$/' => 'LINK',
					'/options$/' => 'OPTION',
					'/post(?:s|meta)$/' => 'POST',
					'/registration_log$/' => 'REGISTRATION',
					'/signups$/' => 'SIGNUP',
					'/site(?:categories|meta)?$/' => 'SITE',
					'/term(?:s|_relationships|_taxonomy|meta)$/' => 'TERM',
					'/user(?:s|meta)$/' => 'USER',
				],

			],

		],

		// The xdebug data source allows an xdebug profiler file to be merged into the metadata. To enable the xdebug extension must be loaded.
		'xdebug' => [

			'enabled' => false,

		],

	],

	'wp_cli' => [

		// bool. Enable or disable data collection for WP-CLI commands.
		'collect' => false,

		// bool. Enable or disable recording of all global parameters. This refers to all global parameters defined via command line arguments, yml config and some WP-CLI defaults.
		'record_global_parameters' => false,

		// bool. Enable or disable recording of runtime global parameters. This refers to global parameters defined via command line arguments only. Ignored if "record_global_parameters" is also true.
		'record_global_runtime_parameters' => true,

		// string[]. List of commands for which data should not be collected.
		'except' => [
			// 'cache get',
		],

		// string[]. List of commands for which data should be collected.
		'only' => [
			// 'cache get',
		],

		// bool. Enable or disable collection of WP-CLI command output.
		'collect_output' => false,

		// bool. Enable or disable collection of data for built-in WP-CLI commands.
		'except_built_in_commands' => true,

	],

	// Configure how metadata is stored.
	'storage' => [

		// string. Storage driver name. Must match one of the keys in the drivers array below.
		'driver' => 'file',

		// int|false. Maximum metadata lifetime in minutes. Set to false to disable metadata expiration.
		'expiration' => 60 * 24 * 7,

		'drivers' => [

			'file' => [

				// int. Directory permissions to use if the driver has to create the storage directory. Should be specified as an octal number.
				'dir_permissions' => 0700,

				// string. Path where metadata is stored. By default it is stored in the wp-content directory, but ideally should be moved somewhere that is not web accessible.
				'path' => WP_CONTENT_DIR . '/cfw-data',

				'compress' => false,

			],

			'sql' => [

				// string|PDO. DSN used by PDO to connect to a database. Can optionally be a pre-existing PDO instance.
				'dsn' => '',

				// string. The table where metadata should be stored. It will be automatically created and updated when needed.
				'table' => 'clockwork',

				// string. Username used to connect to the database.
				'username' => null,

				// string. Password used to connect to the database.
				'password' => null,

			],

		],

	],

	// Configure authentication.
	'authentication' => [

		// bool. Enable or disable authentication.
		'enabled' => false,

		// string. Authentication driver name. Must match one of the keys in the drivers array below.
		'driver' => 'simple',

		'drivers' => [

			'simple' => [

				// string. Password used for simple auth.
				'password' => 'VerySecretPassword',

			],

		],

	],

	// Configure the clockwork serializer.
	'serialization' => [

		// int. Maximum depth of serialized multi-level arrays and objects.
		'depth' => 10,

		// string[]. A list of classes that will never be serialized.
		'blackbox' => [
			Pimple\Container::class,
			Pimple\Psr11\Container::class,
		],

	],

	// Configure stack trace collection.
	'stack_traces' => [

		// bool. Enable or disable stack trace collection.
		'enabled' => true,

		// string[]. List of vendor names to skip when determining caller.
		'skip_vendors' => [],

		// string[]. List of namespaces to skip when determining caller.
		'skip_namespaces' => [],

		// string[]. List of classes to skip when determining caller.
		'skip_classes' => [],

		// int. Limit for the number of frames that will be collected.
		'limit' => 10,

	],

];
