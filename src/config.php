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

	// bool. Enable or disable clockwork.
	'enable' => true,

	// bool. Collect data even when clockwork is disabled.
	'collect_data_always' => false,

	// bool. Enable or disable the the web UI at http://yoursite.test/__clockwork/app.
	'web' => true,

	// bool. Load the clock() global function.
	'register_helpers' => true,

	// array<string, string>. A list of additional headers to send with responses. Header names are prefixed with "X-Clockwork-Header-".
	'headers' => [],

	// Configure HTTP requests data collection.
	'requests' => [

		// bool|string. Only collect data when clockwork extension is open or a request includes a "clockwork-profile" cookie or get/post data key. If set to a string this will be used as a "secret" that must be passed as the "clockwork-profile" value
		'on_demand' => false,

		// bool. Only collect data when the response has a 4xx or 5xx status.
		'errors_only' => false,

		// @todo This feature is likely going to be removed soon. See https://github.com/ssnepenthe/clockwork-for-wp/issues/41.
		'slow_threshold' => 1000,
		'slow_only' => false,

		// bool|int. Sample the collected requests (e.g. set to 100 to only collect 1 in 100 requests).
		'sample' => false,

		// string[]. List of patterns indicating URIs for which data should not be collected.
		'except' => [],

		// string[]. List of patterns indicating URIs for which data should be collected.
		'only' => [],

		// bool. True prevents data collection on OPTIONS requests.
		'except_preflight' => true,

	],

	// Configure data sources. Enable or disable any data source by setting the corresponding "enabled" value. It is unlikely that you will ever modify the "data_source_class" value - this is the ID used to resolve the data source instance from the container.
	'data_sources' => [

		// The conditionals data source records the values of a number of WordPress conditional functions.
		'conditionals' => [

			'enabled' => false,

			'data_source_class' => Conditionals::class,

		],

		// The constants data source records the values of a number of WordPress defined constants.
		'constants' => [

			'enabled' => false,

			'data_source_class' => Constants::class,

		],

		// The core data source records basic WordPress info such as the current version and total execution time.
		'core' => [

			'enabled' => false,

			'data_source_class' => Core::class,

		],

		// The errors data source records PHP errors to the clockwork log.
		// @todo Disabling this data source doesn't currently prevent the error handler from being registered.
		'errors' => [

			'enabled' => false,

			'data_source_class' => Errors::class,

		],

		// The rest api data source records data about all registered rest routes.
		'rest_api' => [

			'enabled' => false,

			'data_source_class' => Rest_Api::class,

		],

		// The theme data source records theme-specific data such as body classes and loaded template parts.
		'theme' => [

			'enabled' => false,

			'data_source_class' => Theme::class,

		],

		// The transients data source records all setted and deleted transients in a given request.
		'transients' => [

			'enabled' => false,

			'data_source_class' => Transients::class,

		],

		// The wp_hook data source records data about all registered hooks.
		'wp_hook' => [

			'enabled' => false,

			'data_source_class' => Wp_Hook::class,

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

			'data_source_class' => Wp_Http::class,

		],

		// The wp_mail data source records mail sent with wp_mail() as well as any errors encountered when sending mail.
		'wp_mail' => [

			'enabled' => false,

			'data_source_class' => Wp_Mail::class,

		],

		// The wp_object_cache data source records data about object cache interaction on a given request.
		'wp_object_cache' => [

			'enabled' => false,

			'data_source_class' => Wp_Object_Cache::class,

		],

		// The wp_query data source records the values of all query vars for a given request.
		'wp_query' => [

			'enabled' => false,

			'data_source_class' => Wp_Query::class,

		],

		// The wp_rewrite data source records all rewrite rules currently registered with WordPress.
		'wp_rewrite' => [

			'enabled' => false,

			'data_source_class' => Wp_Rewrite::class,

		],

		// The wp data sources records some basic data from the global wp instance.
		'wp' => [

			'enabled' => false,

			'data_source_class' => Wp::class,

		],

		// The wpdb data source records all database queries made in the current request and can attempt to identify duplicate queries. To enable you must also defined the "SAVEQUERIES" constant to true.
		'wpdb' => [

			'enabled' => false,

			'data_source_class' => Wpdb::class,

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

			'data_source_class' => Xdebug::class,

		],

	],

	// Configure how metadata is stored.
	'storage' => [

		// string. Storage driver name. Must match one of the keys in the drivers array below.
		'driver' => 'file',

		'drivers' => [

			'file' => [

				'class' => FileStorage::class,

				'config' => [

					// int. Directory permissions to use if the driver has to create the storage directory. Should be specified as an octal number.
					'dir_permissions' => 0700,

					// int|false. Maximum metadata lifetime in minutes. Set to false to disable metadata expiration.
					'expiration' => 60 * 24 * 7,

					// string. Path where metadata is stored. By default it is stored in the wp-content directory, but ideally should be moved somewhere that is not web accessible.
					'path' => WP_CONTENT_DIR . '/cfw-data',

					'compress' => false,

				],

			],

			'sql' => [

				'class' => SqlStorage::class,

				'config' => [

					// string|PDO. DSN used by PDO to connect to a database. Can optionally be a pre-existing PDO instance.
					'dsn' => '',

					// string. The table where metadata should be stored. It will be automatically created and updated when needed.
					'table' => 'clockwork',

					// string. Username used to connect to the database.
					'username' => null,

					// string. Password used to connect to the database.
					'password' => null,

					// int|false. Maximum metadata lifetime in minutes. Set to false to disable metadata expiration.
					'expiration' => 60 * 24 * 7,

				],

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

				'class' => SimpleAuthenticator::class,

				'config' => [

					// string. Password used for simple auth.
					'password' => 'VerySecretPassword',

				],

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
