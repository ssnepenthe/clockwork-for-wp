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
		if ( $container['config']->is_collecting_data() ) {
			$this->listen_to_events( $container );
		}

		if ( ! $container['config']->is_enabled() ) {
			return;
		}

		$this->register_api_routes( $container );

		$container->on( 'wp_loaded', [ 'helpers.request', 'send_headers' ] );

		if ( $container['config']->is_web_enabled() ) {
			$this->register_web_routes( $container );
		}
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
					->addDataSource( new Data_Source\Conditionals() )
					->addDataSource( $c['datasource.http'] )
					->addDataSource( $c['datasource.wp'] );

				if ( $c['config']->is_collecting_cache_data() ) {
					$clockwork->addDataSource(
						new Data_Source\Wp_Object_Cache( $c['wp_object_cache'] )
					);
				}

				if ( $c['config']->is_collecting_db_data() ) {
					$clockwork->addDataSource( new Data_Source\Wpdb( $c['wpdb'] ) );
				}

				if ( $c['config']->is_collecting_email_data() ) {
					$clockwork->addDataSource( $c['datasource.mail'] );
				}

				if ( $c['config']->is_collecting_event_data() ) {
					$clockwork->addDataSource( new Data_Source\Wp_Hook() );
				}

				if ( $c['config']->is_collecting_rewrite_data() ) {
					$clockwork->addDataSource( new Data_Source\Wp_Rewrite( $c['wp_rewrite'] ) );
				}

				if ( $c['config']->is_collecting_theme_data() ) {
					$clockwork->addDataSource( $c['datasource.theme'] );
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

		$container['datasource.http'] =
			/**
			 * @return Wp_Http_Data_Source
			 */
			function( Container $c ) {
				return new Data_Source\Wp_Http();
			};

		$container['datasource.mail'] =
			/**
			 * @return Wp_Mail_Data_Source
			 */
			function( Container $c ) {
				return new Data_Source\Wp_Mail();
			};

		$container['datasource.theme'] =
			/**
			 * @return Theme_Data_Source
			 */
			function( Container $c ) {
				return new Data_Source\Theme();
			};

		$container['datasource.wp'] =
			/**
			 * @return Wp_Data_Source
			 */
			function( Container $c ) {
				return new Data_Source\WordPress();
			};

		$container['helpers.api'] =
			/**
			 * @return Api_Helper
			 */
			function( Container $c ) {
				return new Api_Helper( $c['clockwork.storage'] );
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
				return new Web_Helper();
			};
	}

	/**
	 * @param  Plugin $container
	 * @return void
	 */
	protected function listen_to_events( Plugin $container ) {
		$container['datasource.http']->listen_to_events();

		if ( $container['config']->is_collecting_email_data() ) {
			$container['datasource.mail']->listen_to_events();
		}

		if ( $container['config']->is_collecting_theme_data() ) {
			$container['datasource.theme']->listen_to_events();
		}

		$container['datasource.wp']->listen_to_events();

		$container->on( 'shutdown', [ 'helpers.request', 'finalize_request' ], Plugin::LATE_EVENT );
	}

	/**
	 * @param  Plugin $container
	 * @return void
	 */
	protected function register_api_routes( Plugin $container ) {
		$container
			->on( 'query_vars', [ 'helpers.api', 'register_query_vars' ] )
			->on( 'rewrite_rules_array', [ 'helpers.api', 'register_rewrites' ] )
			->on( 'template_redirect', [ 'helpers.api', 'serve_json' ] );
	}

	protected function register_web_routes( Plugin $container ) {
		$container
			->on( 'query_vars', [ 'helpers.web', 'register_query_vars' ] )
			->on( 'rewrite_rules_array', [ 'helpers.web', 'register_rewrites' ] )
			->on( 'template_redirect', [ 'helpers.web', 'redirect_shortcut' ] )
			->on( 'redirect_canonical', [ 'helpers.web', 'prevent_canonical_redirect' ], 10, 2 )
			->on( 'template_redirect', [ 'helpers.web', 'serve_web_assets' ] );
	}
}
