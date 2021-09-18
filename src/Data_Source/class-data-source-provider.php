<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Config;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use InvalidArgumentException;

final class Data_Source_Provider extends Base_Provider {
	public function register(): void {
		$this->plugin[ Conditionals::class ] = function (): Conditionals {
			$conditionals = $this->plugin->config(
				'data_sources.conditionals.config.conditionals',
				[]
			);

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
						'Conditionals must be of type "array" of with values of type "callable" or "array"'
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
					\array_key_exists( 'label', $conditional )
					&& ! \is_string( $conditional['label'] )
				) {
					throw new InvalidArgumentException(
						'Optional conditional label must be of type "string"'
					);
				}

				if (
					\array_key_exists( 'when', $conditional )
					&& ! \is_callable( $conditional['when'] )
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
		};

		$this->plugin[ Constants::class ] = static function () {
			$constants = [
				'WP_DEBUG',
				'WP_DEBUG_DISPLAY',
				'WP_DEBUG_LOG',
				'SCRIPT_DEBUG',
				'WP_CACHE',
				'CONCATENATE_SCRIPTS',
				'COMPRESS_SCRIPTS',
				'COMPRESS_CSS',
				'WP_LOCAL_DEV',
			];

			if ( \is_multisite() ) {
				$constants[] = 'SUNRISE';
			}

			return new Constants( ...$constants );
		};

		$this->plugin[ Core::class ] = function () {
			return new Core( $this->plugin['wp_version'], $this->plugin['timestart'] );
		};

		$this->plugin[ Errors::class ] = $this->plugin->factory(
			static function () {
				return Errors::get_instance();
			}
		);

		$this->plugin[ Php::class ] = static function () {
			$cookies = \implode( '|', [ AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE ] );

			// @todo Option in plugin config for additional patterns?
			return new Php( '/pass|pwd/i', "/{$cookies}/i" );
		};

		$this->plugin[ Rest_Api::class ] = static function () {
			return new Rest_Api();
		};

		$this->plugin[ Theme::class ] = static function () {
			return new Theme();
		};

		$this->plugin[ Transients::class ] = static function () {
			return new Transients();
		};

		$this->plugin[ Wp_Hook::class ] = function () {
			$config = $this->plugin->config( 'data_sources.wp_hook.config', [] );

			$data_source = new Wp_Hook( $config['all_hooks'] ?? false );

			$tag_filter = new Except_Only_Filter(
				$config['except_tags'] ?? [],
				$config['only_tags'] ?? []
			);

			$data_source->addFilter(
				static function ( $hook ) use ( $tag_filter ) {
					return $tag_filter( $hook['Tag'] );
				}
			);

			$callback_filter = new Except_Only_Filter(
				$config['except_callbacks'] ?? [],
				$config['only_callbacks'] ?? []
			);

			$data_source->addFilter(
				static function ( $hook ) use ( $callback_filter ) {
					return $callback_filter( $hook['Callback'] );
				}
			);

			return $data_source;
		};

		$this->plugin[ Wp_Http::class ] = static function () {
			return new Wp_Http();
		};

		$this->plugin[ Wp_Mail::class ] = static function () {
			return new Wp_Mail();
		};

		$this->plugin[ Wp_Object_Cache::class ] = static function () {
			return new Wp_Object_Cache();
		};

		$this->plugin[ Wp_Query::class ] = static function () {
			return new Wp_Query();
		};

		$this->plugin[ Wp_Redirect::class ] = static function () {
			return new Wp_Redirect();
		};

		$this->plugin[ Wp_Rewrite::class ] = static function () {
			return new Wp_Rewrite();
		};

		$this->plugin[ Wp::class ] = static function () {
			return new Wp();
		};

		$this->plugin[ Wpdb::class ] = function () {
			$config = $this->plugin[ Config::class ]->get( 'data_sources.wpdb.config', [] );

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

			$this->plugin[ Event_Manager::class ]->trigger(
				'cfw_data_sources_wpdb_init',
				$data_source
			);

			return $data_source;
		};

		$this->plugin[ Xdebug::class ] = static function () {
			return new Xdebug();
		};
	}

	public function registered(): void {
		// We have registered our error handler as early as possible in order to collect as many
		// errors as possible. However our config is not available that early so let's apply our
		// configuration now.
		$errors = $this->plugin[ Errors::class ];

		if ( $this->plugin->is_feature_enabled( 'errors' ) ) {
			$config = $this->plugin->config( 'data_sources.errors.config', [] );

			$except_types = $config['except_types'] ?? false;
			$only_types = $config['only_types'] ?? false;

			// Filter errors by type.
			$errors->addFilter(
				static function ( $error ) use ( $except_types, $only_types ) {
					if ( \is_int( $only_types ) ) {
						return ( $error['type'] & $only_types ) > 0;
					}

					if ( \is_int( $except_types ) ) {
						return ( $error['type'] & $except_types ) < 1;
					}

					return true;
				}
			);

			// Filter errors by message pattern.
			$message_filter = new Except_Only_Filter(
				$config['except_messages'] ?? [],
				$config['only_messages'] ?? []
			);

			$errors->addFilter(
				static function ( $error ) use ( $message_filter ) {
					return $message_filter( $error['message'] );
				}
			);

			// Filter errors by file pattern.
			$file_filter = new Except_Only_Filter(
				$config['except_files'] ?? [],
				$config['only_files'] ?? []
			);

			$errors->addFilter(
				static function ( $error ) use ( $file_filter ) {
					return $file_filter( $error['file'] );
				}
			);

			// Filter suppressed errors.
			$include_suppressed = $config['include_suppressed_errors'] ?? false;

			$errors->addFilter(
				static function ( $error ) use ( $include_suppressed ) {
					return ! $error['suppressed'] || $include_suppressed;
				}
			);

			$errors->reapply_filters();
		} else {
			$errors->unregister();
		}
	}

	protected function subscribers(): array {
		$subscribers = [];

		if ( $this->plugin->is_feature_enabled( 'rest_api' ) ) {
			$subscribers[] = Rest_Api::class;
		}

		if ( $this->plugin->is_feature_enabled( 'theme' ) ) {
			$subscribers[] = Theme::class;
		}

		if ( $this->plugin->is_feature_enabled( 'transients' ) ) {
			$subscribers[] = Transients::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_hook' ) ) {
			$subscribers[] = Wp_Hook::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_http' ) ) {
			$subscribers[] = Wp_Http::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_mail' ) ) {
			$subscribers[] = Wp_Mail::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_object_cache' ) ) {
			$subscribers[] = Wp_Object_Cache::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_query' ) ) {
			$subscribers[] = Wp_Query::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_redirect' ) ) {
			$subscribers[] = Wp_Redirect::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_rewrite' ) ) {
			$subscribers[] = Wp_Rewrite::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp' ) ) {
			$subscribers[] = Wp::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wpdb' ) ) {
			$subscribers[] = Wpdb::class;
		}

		if ( $this->plugin->is_feature_enabled( 'xdebug' ) ) {
			$subscribers[] = Xdebug::class;
		}

		return $subscribers;
	}
}
