<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Is;
use Clockwork_For_Wp\Read_Only_Configuration;
use InvalidArgumentException;
use Pimple\Container;

/**
 * @internal
 */
final class Data_Source_Factory {
	private $config;

	private $custom_factories = [];

	private $instances = [];

	private $is;

	private $pimple;

	public function __construct( Read_Only_Configuration $config, Is $is, Container $pimple ) {
		$this->config = $config;
		$this->is = $is;
		$this->pimple = $pimple;
	}

	public function create( string $name, array $config = [] ): DataSource {
		if ( \array_key_exists( $name, $this->instances ) ) {
			return $this->instances[ $name ];
		}

		if ( $this->has_custom_factory( $name ) ) {
			return $this->instances[ $name ] = $this->call_custom_factory( $name, $config );
		}

		$method = "create_{$name}_data_source";

		if ( \method_exists( $this, $method ) ) {
			return $this->instances[ $name ] = ( [ $this, $method ] )( $config );
		}

		throw new InvalidArgumentException( "Unrecognized data source: {$name}" );
	}

	public function get_enabled_data_sources(): array {
		$data_sources = [];

		foreach ( $this->config->get( 'data_sources', [] ) as $name => $data_source ) {
			if (
				( $data_source['enabled'] ?? false ) && $this->is->feature_available( $name )
			) {
				$data_sources[] = $this->create( $name, $data_source['config'] ?? [] );
			}
		}

		return $data_sources;
	}

	public function register_custom_factory( string $name, callable $factory ) {
		$this->custom_factories[ $name ] = $factory;

		return $this;
	}

	private function call_custom_factory( string $name, array $config = [] ): DataSource {
		if ( ! $this->has_custom_factory( $name ) ) {
			throw new InvalidArgumentException( "Cannot call unregistered custom factory {$name}" );
		}

		return ( $this->custom_factories[ $name ] )( $config );
	}

	private function create_conditionals_data_source( array $config ): Conditionals {
		$conditionals = $config['conditionals'] ?? [];

		if ( ! \is_array( $conditionals ) ) {
			throw new InvalidArgumentException( 'Conditionals must be of type "array"' );
		}

		$data_source = new Conditionals();

		foreach ( $conditionals as $conditional ) {
			if ( \is_callable( $conditional ) ) {
				$conditional = [ 'conditional' => $conditional ];
			}

			if ( ! \is_array( $conditional ) ) {
				throw new InvalidArgumentException(
					'Conditionals must be of type "array" with values of type "callable" or "array"'
				);
			}

			if (
				! \array_key_exists( 'conditional', $conditional )
				|| ! \is_callable( $conditional['conditional'] )
			) {
				throw new InvalidArgumentException(
					'Conditionals must be of type "callable" or "array" with value at key "conditional" of type "callable"'
				);
			}

			if (
				\array_key_exists( 'label', $conditional ) && ! \is_string( $conditional['label'] )
			) {
				throw new InvalidArgumentException(
					'Optional conditional label must be of type "string"'
				);
			}

			if (
				\array_key_exists( 'when', $conditional ) && ! \is_callable( $conditional['when'] )
			) {
				throw new InvalidArgumentException(
					'Optional conditional when condition must be of type "callable"'
				);
			}

			$data_source->add_conditional(
				$conditional['conditional'],
				$conditional['label'] ?? null,
				$conditional['when'] ?? null
			);
		}

		return $data_source;
	}

	private function create_constants_data_source( array $config ): Constants {
		return Constants::from( $config );
	}

	private function create_core_data_source(): Core {
		return new Core( $this->pimple[ 'wp_version' ], $this->pimple[ 'timestart' ] );
	}

	private function create_errors_data_source(): Errors {
		return Errors::get_instance();
	}

	private function create_php_data_source( array $config ): Php {
		if ( ! (
			\array_key_exists( 'sensitive_patterns', $config )
			&& \is_array( $config['sensitive_patterns'] )
		) ) {
			$config['sensitive_patterns'] = [];
		}

		return new Php( ...$config['sensitive_patterns'] );
	}

	private function create_rest_api_data_source(): Rest_Api {
		return new Rest_Api();
	}

	private function create_theme_data_source(): Theme {
		return new Theme();
	}

	private function create_transients_data_source(): Transients {
		return new Transients();
	}

	private function create_wp_data_source(): Wp {
		return new Wp();
	}

	private function create_wp_hook_data_source( array $config ): Wp_Hook {
		$data_source = new Wp_Hook( $config['all_hooks'] ?? false );

		$data_source->addFilter(
			( new Filter() )
				->except( $config['except_tags'] ?? [] )
				->only( $config['only_tags'] ?? [] )
				->to_closure( 'Tag' )
		);

		$data_source->addFilter(
			( new Filter() )
				->except( $config['except_callbacks'] ?? [] )
				->only( $config['only_callbacks'] ?? [] )
				->to_closure( 'Callback' )
		);

		return $data_source;
	}

	private function create_wp_http_data_source(): Wp_Http {
		return new Wp_Http();
	}

	private function create_wp_mail_data_source(): Wp_Mail {
		return new Wp_Mail();
	}

	private function create_wp_object_cache_data_source(): Wp_Object_Cache {
		return new Wp_Object_Cache();
	}

	private function create_wp_query_data_source(): Wp_Query {
		return new Wp_Query();
	}

	private function create_wp_redirect_data_source(): Wp_Redirect {
		return new Wp_Redirect();
	}

	private function create_wp_rewrite_data_source(): Wp_Rewrite {
		return new Wp_Rewrite();
	}

	private function create_wpdb_data_source( array $config ): Wpdb {
		$data_source = new Wpdb(
			$config['detect_duplicate_queries'] ?? false,
			$config['pattern_model_map'] ?? []
		);

		if ( $config['slow_only'] ?? false ) {
			$slow_threshold = $config['slow_threshold'] ?? 50;

			$data_source->addFilter(
				static function ( $query ) use ( $slow_threshold ) {
					// @todo Should this be inclusive (i.e. >=) instead?
					return $query['duration'] > $slow_threshold;
				}
			);
		}

		$this->pimple[ Event_Manager::class ]->trigger(
			'cfw_data_sources_wpdb_init',
			$data_source
		);

		return $data_source;
	}

	private function create_xdebug_data_source(): Xdebug {
		return new Xdebug();
	}

	private function has_custom_factory( string $name ): bool {
		return \array_key_exists( $name, $this->custom_factories );
	}
}
