<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork_For_Wp\Base_Factory;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Globals;
use Clockwork_For_Wp\Is;
use Clockwork_For_Wp\Read_Only_Configuration;
use InvalidArgumentException;
use Pimple\Container;

/**
 * @internal
 */
final class Data_Source_Factory extends Base_Factory {
	protected bool $cache_enabled = true;

	private $config;

	private $is;

	private $pimple;

	public function __construct( Read_Only_Configuration $config, Is $is, Container $pimple ) {
		$this->config = $config;
		$this->is = $is;
		$this->pimple = $pimple;
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

	protected function create_conditionals_instance( array $config ): Conditionals {
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

	protected function create_constants_instance( array $config ): Constants {
		return Constants::from( $config );
	}

	protected function create_core_instance(): Core {
		return new Core( Globals::get( 'wp_version' ), Globals::get( 'timestart' ) );
	}

	protected function create_errors_instance(): Errors {
		return Errors::get_instance();
	}

	protected function create_php_instance( array $config ): Php {
		if ( ! (
			\array_key_exists( 'sensitive_patterns', $config )
			&& \is_array( $config['sensitive_patterns'] )
		) ) {
			$config['sensitive_patterns'] = [];
		}

		return new Php( ...$config['sensitive_patterns'] );
	}

	protected function create_rest_api_instance(): Rest_Api {
		return new Rest_Api();
	}

	protected function create_theme_instance(): Theme {
		return new Theme();
	}

	protected function create_transients_instance(): Transients {
		return new Transients();
	}

	protected function create_wp_hook_instance( array $config ): Wp_Hook {
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

	protected function create_wp_http_instance(): Wp_Http {
		return new Wp_Http();
	}

	protected function create_wp_instance(): Wp {
		return new Wp();
	}

	protected function create_wp_mail_instance(): Wp_Mail {
		return new Wp_Mail();
	}

	protected function create_wp_object_cache_instance(): Wp_Object_Cache {
		return new Wp_Object_Cache();
	}

	protected function create_wp_query_instance(): Wp_Query {
		return new Wp_Query();
	}

	protected function create_wp_redirect_instance(): Wp_Redirect {
		return new Wp_Redirect();
	}

	protected function create_wp_rewrite_instance(): Wp_Rewrite {
		return new Wp_Rewrite();
	}

	protected function create_wpdb_instance( array $config ): Wpdb {
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

	protected function create_xdebug_instance(): Xdebug {
		return new Xdebug();
	}
}
