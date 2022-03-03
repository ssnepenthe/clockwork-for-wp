<?php

declare(strict_types=1);

use Nette\Schema\Expect;

return [
	// Enable or disable clockwork.
	'enable' => Expect::bool()->default( true ),

	// Collect data even when clockwork is disabled.
	'collect_data_always' => Expect::bool()->default( false ),

	// Enable or disable collection of client metrics.
	'collect_client_metrics' => Expect::bool()->default( true ),

	// Enable or disable collection of admin-ajax "heartbeat" requests.
	'collect_heartbeat' => Expect::bool()->default( true ),

	// Enable or disable the the web UI at http://yoursite.test/__clockwork/app.
	'web' => Expect::bool()->default( true ),

	// Enable or disable the Clockwork browser toolbar.
	'toolbar' => Expect::bool()->default( true ),

	// Load the clock() global function.
	'register_helpers' => Expect::bool()->default( true ),

	// A list of additional headers to send with responses. Array keys are used as header names and prefixed with "X-Clockwork-Header-", values are used for header values.
	'headers' => Expect::arrayOf( Expect::string(), Expect::string() )->default( [] ),

	// Configure HTTP requests data collection.
	'requests' => Expect::structure( [

		// Only collect data when clockwork extension is open or a request includes a "clockwork-profile" cookie or get/post data key. If set to a string this will be used as a "secret" that must be passed as the "clockwork-profile" value.
		'on_demand' => Expect::anyOf( Expect::bool(), Expect::string() )->default( false ),

		// Sample the collected requests (e.g. set to 100 to only collect 1 in 100 requests). Set to false to disable sampling.
		'sample' => Expect::anyOf( false, Expect::int() )->default( false ),

		// List of patterns indicating URIs for which data should not be collected.
		'except' => Expect::listOf( Expect::string() )->default( [] ),

		// List of patterns indicating URIs for which data should be collected.
		'only' => Expect::listOf( Expect::string() )->default( [] ),

		// True prevents data collection on OPTIONS requests.
		'except_preflight' => Expect::bool()->default( true ),

		// List of patterns indicating sensitive data that should be redacted from request input (get, post, cookies, session, etc.).
		'sensitive_patterns' => Expect::listOf( Expect::string() )->default( [] ),
	]),

	// Configure data sources. Enable or disable any data source by setting the corresponding "enabled" value.
	'data_sources' => Expect::structure( [

		// The conditionals data source records the values of a number of WordPress conditional functions.
		'conditionals' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

			'config' => Expect::structure( [

				// List of conditionals to check. Can optionally be provided as an array with required key 'conditional' and optional keys 'label' and 'when'. When provided as array, 'conditional' refers to the conditional to check, 'label' allows you to override the label displayed in Clockwork and 'when' is a callable that allows check for this conditional to be disabled at runtime. Real default is set in defaults.php.
				'conditionals' => Expect::listOf(
					Expect::anyOf(
						// @todo Expect::callable() works but verifies syntax only, so not guaranteed to be callable...
						Expect::mixed()->assert( 'is_callable' ),
						Expect::structure( [
							'conditional' => Expect::mixed()->assert( 'is_callable' )->required(),
							'label' => Expect::string(),
							'when' => Expect::mixed()->assert( 'is_callable' ),
						] )->skipDefaults()
					),
				),

			]),

		]),

		// The constants data source records the values of a number of WordPress defined constants.
		'constants' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

			'config' => Expect::structure( [

				// List of constants to check. Can optionally be provided as an array with required key 'constant' and optional key 'when'. When provided as array, 'constant' refers to the constant to check and 'when' is a callable that allows the check of this constant to be disabled at runtime. Real default is set in defaults.php.
				'constants' => Expect::listOf(
					Expect::anyOf(
						Expect::string(),
						Expect::structure( [
							'constant' => Expect::string()->required(),
							'when' => Expect::mixed()->assert( 'is_callable' ),
						] )->skipDefaults(),
					)
				),

			]),

		]),

		// The core data source records basic WordPress info such as the current version and total execution time.
		'core' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		]),

		// The errors data source records PHP errors to the clockwork log.
		'errors' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

			'config' => Expect::structure( [

				// Bitmask indicating error types that should not be recorded. False to disable.
				'except_types' => Expect::anyOf( false, Expect::int() )->default( false ),

				// Bitmask indicating error types that should be recorded. False to disable.
				'only_types' => Expect::anyOf( false, Expect::int() )->default( false ),

				// A list of message patterns indicating an error should not be recorded.
				'except_messages' => Expect::listOf( Expect::string() )->default( [] ),

				// A list of message patterns indicating an error should be recorded.
				'only_messages' => Expect::listOf( Expect::string() )->default( [] ),

				// A list of file patterns indicating an error should not be recorded.
				'except_files' => Expect::listOf( Expect::string() )->default( [] ),

				// A list of file patterns indicating an error should be recorded.
				'only_files' => Expect::listOf( Expect::string() )->default( [] ),

				// Enable or disable collection of @-suppressed errors.
				'include_suppressed_errors' => Expect::bool()->default( false ),

			] ),

		] ),

		// The rest api data source records data about all registered rest routes.
		'rest_api' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

		// The theme data source records theme-specific data such as body classes and loaded template parts.
		'theme' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

		// The transients data source records all setted and deleted transients in a given request.
		'transients' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

		// The wp_hook data source records data about all registered hooks.
		'wp_hook' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

			'config' => Expect::structure( [

				// A list of patterns indicating hook names that shouldn't be recorded.
				'except_tags' => Expect::listOf( Expect::string() )->default( [] ),

				// A list of patterns indicating hook names that should be recorded. Takes precedence over "except_tags".
				'only_tags' => Expect::listOf( Expect::string() )->default( [] ),

				// A list of patterns indicating callback names that shouldn't be recorded.
				'except_callbacks' => Expect::listOf( Expect::string() )->default( [
					'clockwork-for-wp',
				] ),

				// A list of patterns indicating callback names that should be recorded. Takes precedence over "except_callbacks".
				'only_callbacks' => Expect::listOf( Expect::string() )->default( [] ),

				// When set to true it will record data about all registered hooks instead of just the ones triggered for the current request.
				'all_hooks' => Expect::bool()->default( false ),

			] ),

		] ),

		// The wp_http data source records data about HTTP requests made with the WP HTTP API to the clockwork log and timeline.
		'wp_http' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

		// The wp_mail data source records mail sent with wp_mail() as well as any errors encountered when sending mail.
		'wp_mail' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

		// The wp_object_cache data source records data about object cache interaction on a given request.
		'wp_object_cache' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

		// The wp_query data source records the values of all query vars for a given request.
		'wp_query' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

		// The wp_redirect data source adds some redirect context to the clockwork log.
		'wp_redirect' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

		// The wp_rewrite data source records all rewrite rules currently registered with WordPress.
		'wp_rewrite' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

		// The wp data sources records some basic data from the global wp instance.
		'wp' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

		// The wpdb data source records all database queries made in the current request and can attempt to identify duplicate queries. To enable you must also defined the "SAVEQUERIES" constant to true.
		'wpdb' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

			'config' => Expect::structure( [

				// When true it will add duplicate queries with counts to the clockwork log.
				'detect_duplicate_queries' => Expect::bool()->default( false ),

				// When true only database queries that take longer than "slow_threshold" below are collected.
				'slow_only' => Expect::bool()->default( false ),

				// Time in milliseconds after which a query will be marked as slow. Disregarded unless "slow_only" is true.
				'slow_threshold' => Expect::int()->default( 50 ),

				// A map of regular expressions to "models". Used to assign a model to a query in the database tab. If you need more than a simple regular expression to determine a query model, use the "cfw_data_sources_wpdb_init" hook.
				'pattern_model_map' => Expect::arrayOf( Expect::string(), Expect::string() )->default( [
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
				] ),

			] ),

		] ),

		// The xdebug data source allows an xdebug profiler file to be merged into the metadata. To enable the xdebug extension must be loaded.
		'xdebug' => Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] ),

	] )->otherItems(
		Expect::structure( [

			'enabled' => Expect::bool()->default( false ),

		] )->otherItems()
	),

	'wp_cli' => Expect::structure( [

		// Enable or disable data collection for WP-CLI commands.
		'collect' => Expect::bool()->default( false ),

		// Enable or disable recording of all global parameters. This refers to all global parameters defined via command line arguments, yml config and some WP-CLI defaults.
		'record_global_parameters' => Expect::bool()->default( false ),

		// Enable or disable recording of runtime global parameters. This refers to global parameters defined via command line arguments only. Ignored if "record_global_parameters" is also true.
		'record_global_runtime_parameters' => Expect::bool()->default( true ),

		// List of commands for which data should not be collected.
		'except' => Expect::listOf( Expect::string() )->default( [
			// 'cache get'
		] ),

		// List of commands for which data should be collected.
		'only' => Expect::listOf( Expect::string() )->default( [
			// 'cache get'
		] ),

		// Enable or disable collection of WP-CLI command output.
		'collect_output' => Expect::bool()->default( false ),

		// Enable or disable collection of data for built-in WP-CLI commands.
		'except_built_in_commands' => Expect::bool()->default( true ),

	] ),

	// Configure how metadata is stored.
	'storage' => Expect::structure( [

		// Storage driver name. Must match one of the keys in the drivers array below.
		'driver' => Expect::string()->default( 'file' ),

		// Maximum metadata lifetime in minutes. Set to null to disable metadata expiration.
		'expiration' => Expect::int()->default( 60 * 24 * 7 )->nullable(),

		'drivers' => Expect::structure( [

			'file' => Expect::structure( [

				// Directory permissions to use if the driver has to create the storage directory. Should be specified as an octal number.
				'dir_permissions' => Expect::int()->default( 0700 ),

				// Path where metadata is stored. Real default is set in defaults.php to the wp-content directory, but ideally should be moved somewhere that is not web accessible.
				'path' => Expect::string()->default( '' ),

				// Whether or not metadata files should be compressed.
				'compress' => Expect::bool()->default( false ),

				// Maximum metadata lifetime in minutes. Overrides global "storage.expiration" option above if set to something other than null.
				'expiration' => Expect::int()->nullable(),

			] ),

			'sql' => Expect::structure( [

				// DSN used by PDO to connect to a database. Can optionally be a pre-existing PDO instance.
				'dsn' => Expect::anyOf( Expect::string(), Expect::type( PDO::class ) )->default( '' ),

				// The table where metadata should be stored. It will be automatically created and updated when needed.
				'table' => Expect::string()->default( 'clockwork' ),

				// Username used to connect to the database.
				'username' => Expect::string()->nullable(),

				// Password used to connect to the database.
				'password' => Expect::string()->nullable(),

				// Maximum metadata lifetime in minutes. Overrides global "storage.expiration" option above if set to something other than null.
				'expiration' => Expect::int()->nullable(),

			] ),

		] )->otherItems( Expect::array() ),

	] ),

	// Configure authentication.
	'authentication' => Expect::structure( [

		// Enable or disable authentication.
		'enabled' => Expect::bool()->default( false ),

		// Authentication driver name. Must match one of the keys in the drivers array below.
		'driver' => Expect::string()->default( 'simple' ),

		'drivers' => Expect::structure( [

			'simple' => Expect::structure( [

				// Password used for simple auth.
				'password' => Expect::string()->default( 'VerySecretPassword' ),

			] ),

		] )->otherItems( Expect::array() ),

	] ),

	// Configure the clockwork serializer.
	'serialization' => Expect::structure( [

		// Maximum depth of serialized multi-level arrays and objects.
		'depth' => Expect::int()->default( 10 ),

		// A list of classes that will never be serialized.
		'blackbox' => Expect::listOf( Expect::string() )->default( [
			Pimple\Container::class,
			Pimple\Psr11\Container::class,
		] ),

	] ),

	// Configure stack trace collection.
	'stack_traces' => Expect::structure( [

		// Enable or disable stack trace collection.
		'enabled' => Expect::bool()->default( true ),

		// List of vendor names to skip when determining caller.
		'skip_vendors' => Expect::listOf( Expect::string() )->default( [] ),

		// List of namespaces to skip when determining caller.
		'skip_namespaces' => Expect::listOf( Expect::string() )->default( [] ),

		// List of classes to skip when determining caller.
		'skip_classes' => Expect::listOf( Expect::string() )->default( [] ),

		// Limit for the number of frames that will be collected.
		'limit' => Expect::int()->default( 10 ),

	] ),

];
