<?php

namespace Cfw_Test_Helper;

class Config_Fetcher {
	protected $input;

	public function __construct( array $input = [] ) {
		$this->input = $input;
	}

	public function get_config() {
		$config = [];

		foreach ( $this->config_caster_map() as $key => $caster ) {
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

	protected function config_caster_map() {
		return [
			'enable' => [ $this, 'boolean' ],
			'collect_data_always' => [ $this, 'boolean' ],
			'collect_client_metrics' => [ $this, 'boolean' ],
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
