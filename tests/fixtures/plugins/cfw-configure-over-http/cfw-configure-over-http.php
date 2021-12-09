<?php

namespace Cfw_Configure_Over_Http;

/**
 * Plugin Name: CFW Configure Over HTTP
 * Plugin URI: https://github.com/ssnepenthe/clockwork-for-wp
 * Description: A plugin for configuring Clockwork on the fly from our browser test suite.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: MIT
 */

function deactivate() {
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

	\deactivate_plugins( __FILE__ );
}

function notify( $message ) {
	echo '<div class="notice notice-error">';
	echo '<p>CFW Configure Over HTTP deactivated: ';
	echo \esc_html( $message );
	echo '</p>';
	echo '</div>';
}

if ( ! \function_exists( 'wp_get_environment_type' ) ) {
	\add_action( 'admin_init', __NAMESPACE__ . '\\deactivate' );
	\add_action( 'admin_notices', function() {
		notify( 'This plugin requires WordPress version 5.5.0 or greater' );
	} );
	return;
}

if ( 'production' === \wp_get_environment_type() ) {
	\add_action( 'admin_init', __NAMESPACE__ . '\\deactivate' );
	\add_action( 'admin_notices', function() {
		notify( 'This plugin can only run on non-production environments' );
	} );
	return;
}

if ( ! \function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! \is_plugin_active( 'clockwork-for-wp/clockwork-for-wp.php' ) ) {
	\add_action( 'admin_init', __NAMESPACE__ . '\\deactivate' );
	\add_action( 'admin_notices', function() {
		notify( 'This plugin requires Clockwork for WP to be installed and active' );
	} );
	return;
}

class Config_Fetcher {
	protected $input;

	public function __construct( array $input = [] ) {
		$this->input = $input;
	}

	public function get_config() {
		$config = [];

		foreach ( $this->desired_configs() as $key => $caster ) {
			$value = \Clockwork_For_Wp\array_get( $this->input, $key );

			if ( null === $value ) {
				continue;
			}

			$value = $caster( $value );

			if ( null === $value ) {
				continue;
			}

			$config[ $key ] = $value;
		}

		return $config;
	}

	protected function desired_configs() {
		return [
			'enable' => [ $this, 'boolean' ],
			'collect_data_always' => [ $this, 'boolean' ],
			'web' => [ $this, 'boolean' ],
			'register_helpers' => [ $this, 'boolean' ],
			'headers' => [ $this, 'associative_array_of_strings' ],

			'requests.on_demand' => [ $this, 'boolean' ],
			'requests.errors_only' => [ $this, 'boolean' ],
			'requests.slow_threshold' => [ $this, 'integer' ],
			'requests.slow_only' => [ $this, 'boolean' ],
			'requests.sample' => [ $this, 'boolean' ],
			'requests.except' => [ $this, 'except' ],
			'requests.only' => [ $this, 'array_of_strings' ],
			'requests.except_preflight' => [ $this, 'boolean' ],

			'data_sources.conditionals.enabled' => [ $this, 'boolean' ],
			'data_sources.constants.enabled' => [ $this, 'boolean' ],
			'data_sources.core.enabled' => [ $this, 'boolean' ],
			'data_sources.errors.enabled' => [ $this, 'boolean' ],
			'data_sources.rest_api.enabled' => [ $this, 'boolean' ],
			'data_sources.theme.enabled' => [ $this, 'boolean' ],
			'data_sources.transients.enabled' => [ $this, 'boolean' ],
			'data_sources.wp_hook.enabled' => [ $this, 'boolean' ],
			'data_sources.wp_hook.config.except_tags' => [ $this, 'array_of_strings' ],
			'data_sources.wp_hook.config.only_tags' => [ $this, 'array_of_strings' ],
			'data_sources.wp_hook.config.except_callbacks' => [ $this, 'array_of_strings' ],
			'data_sources.wp_hook.config.only_callbacks' => [ $this, 'array_of_strings' ],
			'data_sources.wp_http.enabled' => [ $this, 'boolean' ],
			'data_sources.wp_mail.enabled' => [ $this, 'boolean' ],
			'data_sources.wp_object_cache.enabled' => [ $this, 'boolean' ],
			'data_sources.wp_query.enabled' => [ $this, 'boolean' ],
			'data_sources.wp_rewrite.enabled' => [ $this, 'boolean' ],
			'data_sources.wp.enabled' => [ $this, 'boolean' ],
			'data_sources.wpdb.enabled' => [ $this, 'boolean' ],
			'data_sources.wpdb.config.detect_duplicate_queries' => [ $this, 'boolean' ],
			'data_sources.wpdb.config.slow_only' => [ $this, 'boolean' ],
			'data_sources.wpdb.config.slow_threshold' => [ $this, 'float' ],
			'data_sources.xdebug.enabled' => [ $this, 'boolean' ],

			// @todo Will we ever want to configure storage for tests?

			'authentication.enabled' => [ $this, 'boolean' ],
			'authentication.drivers.simple.config.password' => [ $this, 'string' ],

			// @todo Will we ever want to configure serialization for tests?
			// @todo Will we ever want to configure stack traces for tests?
		];
	}

	protected function associative_array_of_strings( $value ) {
		if ( null === $this->array_of_strings( array_keys( $value ) ) ) {
			return null;
		}

		if ( null === $this->array_of_strings( $value ) ) {
			return null;
		}

		return $value;
	}

	protected function array_of_strings( $value ) {
		if ( ! \is_array( $value ) ) {
			return null;
		}

		$string_count = \count( \array_filter( $value, 'is_string' ) );

		if ( $string_count !== \count( $value ) ) {
			return null;
		}

		return \array_values( $value );
	}

	protected function boolean( $value ) {
		return \filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	protected function except( $value ) {
		// We are providing a pattern via query string that will be used to exclude specific URLs
		// but the pattern will always be present in the current URL. Encode to get around this.
		$value = $this->array_of_strings( $value );

		if ( null === $value ) {
			return null;
		}

		return array_map( function( $val ) {
			return \Base64Url\Base64Url::decode( $val );
		}, $value );
	}

	protected function float( $value ) {
		if ( ! \is_numeric( $value ) ) {
			return null;
		}

		return (float) $value;
	}

	protected function integer( $value ) {
		if ( ! \is_numeric( $value ) ) {
			return null;
		}

		return (int) $value;
	}

	protected function string( $value ) {
		return (string) $value;
	}
}

// @todo Having some issues with the FileStorage implementation where oldest file is disregarded.
//       Use a rough manual implementation for now and revisit after Clockwork v6 drops.
//       https://github.com/itsgoingd/clockwork/issues/510
class Metadata {
	public static function dir() {
		$config = \_cfw_instance()[ \Clockwork_For_Wp\Config::class ];

		if ( 'file' !== $config->get( 'storage.driver' ) ) {
			throw new \RuntimeException(
				'Clockwork storage driver must be set to "file" for tests'
			);
		}

		return rtrim( $config->get( 'storage.drivers.file.config.path' ), '/\\' );
	}

	public static function list_all() {
		return \glob( static::dir() . '/*.json' );
	}

	public static function list_all_with_index() {
		$dir = static::dir();

		return \glob( "{{$dir}/*.json,{$dir}/index}", \GLOB_BRACE );
	}

	public static function cleanup() {
		foreach ( static::list_all_with_index() as $file ) {
			\unlink( $file );
		}
	}

	public static function find( $id ) {
		$file = static::dir() . "/{$id}.json";

		if ( ! \is_readable( $file ) ) {
			if ( ! \file_exists( $file ) ) {
				throw new \InvalidArgumentException( "Metadata with ID {$id} does not exist" );
			} else {
				throw new \RuntimeException( "Metadata with ID {$id} not readable" );
			}
		}

		$request = \json_decode( \file_get_contents( $file ), true );

		if ( null === $request || \JSON_ERROR_NONE !== \json_last_error() ) {
			throw new \RuntimeException( "Metadata with ID {$id} contains invalid JSON" );
		}

		return $request;
	}
}

\add_action( 'cfw_config_init', function( $config ) {
	foreach ( ( new Config_Fetcher( $_GET ) )->get_config() as $key => $value ) {
		$config->set( $key, $value );
	}

	$requests_except = $config->get( 'requests.except', [] );
	$requests_except[] = 'action=cfw_coh_';

	$config->set( 'requests.except', $requests_except );
} );

\add_action( 'wp_footer', function() {
	printf(
		'<span data-cy="ajaxurl">%s</span><span data-cy="request-id">%s</span>',
		\esc_html( \admin_url( 'admin-ajax.php', 'relative' ) ),
		\esc_html( \_cfw_instance()[ \Clockwork\Request\Request::class ]->id )
	);
} );

function metadata_by_id() {
	if ( ! \array_key_exists( 'id', $_REQUEST ) ) {
		\wp_send_json_error();
	}

	try {
		$request = Metadata::find( $_REQUEST['id'] );

		\wp_send_json_success( $request );
	} catch ( \Exception $e ) {
		\wp_send_json_error();
	}
}
\add_action( 'wp_ajax_cfw_coh_metadata_by_id', __NAMESPACE__ . '\\metadata_by_id' );
\add_action( 'wp_ajax_nopriv_cfw_coh_metadata_by_id', __NAMESPACE__ . '\\metadata_by_id' );

function metadata_count() {
	try {
		$file_list = Metadata::list_all();

		\wp_send_json_success( \count( $file_list ) );
	} catch ( \Exception $e ) {
		\wp_send_json_error();
	}
}
\add_action( 'wp_ajax_cfw_coh_metadata_count', __NAMESPACE__ . '\\metadata_count' );
\add_action( 'wp_ajax_nopriv_cfw_coh_metadata_count', __NAMESPACE__ . '\\metadata_count' );

function clean_metadata() {
	try {
		Metadata::cleanup();

		\wp_send_json_success();
	} catch ( \Exception $e ) {
		\wp_send_json_error();
	}
}
\add_action( 'wp_ajax_cfw_coh_clean_metadata', __NAMESPACE__ . '\\clean_metadata' );
\add_action( 'wp_ajax_nopriv_cfw_coh_clean_metadata', __NAMESPACE__ . '\\clean_metadata' );
