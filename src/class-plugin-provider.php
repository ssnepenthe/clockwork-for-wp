<?php

namespace Clockwork_For_Wp;

use Pimple\Container;
use Clockwork\Clockwork;
use Clockwork\Storage\FileStorage;
use Clockwork\DataSource\PhpDataSource;
use Pimple\ServiceProviderInterface as Provider;

class Plugin_Provider implements Provider, Bootable_Provider {
	/**
	 * @param  Plugin $container
	 * @return void
	 */
	public function boot( Plugin $container ) {
		$this->boot_routes( $container );

		if ( $container['config']->is_collecting_data() ) {
			$this->boot_datasources( $container );

			$container->on(
				'shutdown',
				[ 'helpers.request', 'finalize_request' ],
				Plugin::LATE_EVENT
			);
		}

		if ( $container['config']->is_enabled() ) {
			$container->on( 'wp_loaded', [ 'helpers.request', 'send_headers' ] );
		}

		$this->boot_api( $container );
		$this->boot_web_app( $container );
	}

	/**
	 * @param  Container $container
	 * @return void
	 */
	public function register( Container $container ) {
		$container['config'] =
			/**
			 * @return Config
			 */
			function( Container $c ) {
				$args = apply_filters( 'cfw_config_args', [] );

				$config = new Config( $args );

				do_action( 'cfw_config_init', $config );

				return $config;
			};

		$container['clockwork'] =
			/**
			 * @return Clockwork
			 */
			function( Container $c ) {
				$clockwork = new Clockwork();

				$clockwork
					->addDataSource( new PhpDataSource() )
					// @todo Should these be conditionally added?
					->addDataSource( $c['datasource.errors'] )
					->addDataSource( $c['datasource.http'] )
					->addDataSource( $c['datasource.rest'] )
					->addDataSource( $c['datasource.wp'] );

				if ( $c['config']->is_collecting_cache_data() ) {
					$clockwork->addDataSource( $c['datasource.cache'] );
				}

				if ( $c['config']->is_collecting_db_data() ) {
					$clockwork->addDataSource( new Data_Source\Wpdb( $c['wpdb'] ) );
				}

				if ( $c['config']->is_collecting_email_data() ) {
					$clockwork->addDataSource( $c['datasource.mail'] );
				}

				if ( $c['config']->is_collecting_event_data() ) {
					$clockwork->addDataSource( $c['datasource.hook'] );
				}

				if ( $c['config']->is_collecting_rewrite_data() ) {
					$clockwork->addDataSource( new Data_Source\Wp_Rewrite( $c['wp_rewrite'] ) );
				}

				if ( $c['config']->is_collecting_theme_data() ) {
					$clockwork->addDataSource( $c['datasource.theme'] );
				}

				if ( in_array( 'xdebug', get_loaded_extensions(), true ) ) {
					$clockwork->addDataSource( $c['datasource.xdebug'] );
				}

				$clockwork->setStorage( $c['clockwork.storage'] );

				return $clockwork;
			};

		$container['clockwork.storage'] =
			/**
			 * @return \Clockwork\Storage\StorageInterface
			 */
			function( Container $c ) {
				$storage = new FileStorage(
					$c['config']->get_storage_files_path(),
					0700,
					$c['config']->get_storage_expiration()
				);

				$storage->filter = $c['config']->get_filter();

				return $storage;
			};

		$container['routes'] =
			function( Container $c ) {
				return new Route_Manager( $c['wp'] );
			};

		$this->register_datasources( $container );
		$this->register_helpers( $container );
	}

	/**
	 * @param  Plugin $container
	 * @return void
	 */
	protected function boot_api( Plugin $container ) {
		if ( $container['config']->is_enabled() ) {
			$container
				->on( 'init', [ 'helpers.api', 'register_routes' ] )
				->on( 'template_redirect', [ 'helpers.api', 'serve_json' ] );
		}
	}

	/**
	 * @param  Plugin $container
	 * @return void
	 */
	protected function boot_datasources( Plugin $container ) {
		$container['datasource.errors']->listen_to_events();
		$container['datasource.http']->listen_to_events();

		if ( $container['config']->is_collecting_cache_data() ) {
			$container['datasource.cache']->listen_to_events();
		}

		if ( $container['config']->is_collecting_email_data() ) {
			$container['datasource.mail']->listen_to_events();
		}

		if ( $container['config']->is_collecting_theme_data() ) {
			$container['datasource.theme']->listen_to_events();
		}

		$container['datasource.wp']->listen_to_events();

		if ( in_array( 'xdebug', get_loaded_extensions(), true ) ) {
			$container['datasource.xdebug']->listen_to_events();
		}
	}

	protected function boot_routes( Plugin $container ) {
		// @todo Should these all run late?
		$container
			->on( 'option_rewrite_rules', [ 'routes', 'merge_rewrite_rules' ] )
			->on( 'pre_update_option_rewrite_rules', [ 'routes', 'diff_rewrite_rules' ] )
			->on( 'query_vars', [ 'routes', 'merge_query_vars' ] )
			->on( 'rewrite_rules_array', [ 'routes', 'merge_rewrite_rules' ] )
			->on( 'template_redirect', [ 'routes', 'call_matched_handler' ], Plugin::LATE_EVENT );
	}

	protected function boot_web_app( Plugin $container ) {
		// @todo Should ->is_web_enabled() check ->is_enabled() internally?
		// @todo Or maybe we should we allow the web app to be served even when clockwork is disabled?
		if ( $container['config']->is_enabled() && $container['config']->is_web_enabled() ) {
			$container
				->on( 'init', [ 'helpers.web', 'register_routes' ] )
				->on( 'template_redirect', [ 'helpers.web', 'redirect_shortcut' ] )
				->on( 'redirect_canonical', [ 'helpers.web', 'prevent_canonical_redirect' ], 10, 2 )
				->on( 'template_redirect', [ 'helpers.web', 'serve_web_assets' ] );
		}
	}

	protected function register_datasources( Plugin $container ) {
		$container['datasource.cache'] =
			/**
			 * @return Data_Source\Cache
			 */
			function( Container $c ) {
				return new Data_Source\Cache( $c['wp_object_cache'] );
			};

		$container['datasource.errors'] =
			/**
			 * @return Data_Source\Errors
			 */
			function( Container $c ) {
				return new Data_Source\Errors();
			};

		$container['datasource.hook'] =
			function( Container $c ) {
				return new Data_Source\Wp_Hook();
			};

		$container['datasource.http'] =
			/**
			 * @return Data_Source\Wp_Http
			 */
			function( Container $c ) {
				return new Data_Source\Wp_Http();
			};

		$container['datasource.mail'] =
			/**
			 * @return Data_Source\Wp_Mail
			 */
			function( Container $c ) {
				return new Data_Source\Wp_Mail();
			};

		$container['datasource.rest'] =
			function( Container $c ) {
				return new Data_Source\Rest_Api( $c['wp_rest_server'] );
			};

		$container['datasource.theme'] =
			/**
			 * @return Data_Source\Theme
			 */
			function( Container $c ) {
				$source = new Data_Source\Theme();
				$dep_handler = function() use ( $c, $source ) {
					$source->set_content_width( $c['content_width'] );
				};

				if ( did_action( 'init' ) ) {
					$dep_handler();
				} else {
					add_action( 'init', $dep_handler, Plugin::EARLY_EVENT );
				}

				return $source;
			};

		$container['datasource.wp'] =
			/**
			 * @return Data_Source\WordPress
			 */
			function( Container $c ) {
				$source = new Data_Source\WordPress( $c['timestart'] );
				$dep_handler = function() use ( $c, $source ) {
					$source->set_wp( $c['wp'] );
					$source->set_wp_query( $c['wp_query'] );
				};

				if ( did_action( 'init' ) ) {
					$dep_handler();
				} else {
					add_action( 'init', $dep_handler, Plugin::EARLY_EVENT );
				}

				return $source;
			};

		$container['datasource.xdebug'] =
			/**
			 * @return Data_Source\Xdebug
			 */
			function( Container $c ) {
				return new Data_Source\Xdebug();
			};
	}

	protected function register_helpers( Plugin $container ) {
		$container['helpers.api'] =
			/**
			 * @return Api_Helper
			 */
			function( Container $c ) {
				return new Api_Helper( $c['clockwork'], $c['clockwork.storage'], $c['routes'] );
			};

		$container['helpers.request'] =
			/**
			 * @return Request_Helper
			 */
			function( Container $c ) {
				return new Request_Helper( $c['clockwork'], $c['config'] );
			};

		$container['helpers.web'] =
			/**
			 * @return Web_Helper
			 */
			function( Container $c ) {
				return new Web_Helper( $c['routes'] );
			};
	}
}
