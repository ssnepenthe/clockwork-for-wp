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

		$this->register_api_rewrites( $container );

		$container->on( 'template_redirect', [ 'helpers.request', 'send_headers' ] );
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
				return new Config();
			};

		$container['clockwork'] =
			/**
			 * @return Clockwork
			 */
			function( Container $c ) {
				$clockwork = new Clockwork();

				$clockwork
					->addDataSource( new PhpDataSource() )
					->setStorage( $c['clockwork.storage'] );

				return $clockwork;
			};

		$container['clockwork.storage'] =
			/**
			 * @return \Clockwork\Storage\StorageInterface
			 */
			function( Container $c ) {
				// @todo Move params to config.
				$storage = new FileStorage(
					$c['config']->get_storage_files_path(),
					0700,
					$c['config']->get_storage_expiration()
				);

				$storage->filter = $c['config']->get_filter();

				return $storage;
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
	}

	/**
	 * @param  Plugin $container
	 * @return void
	 */
	protected function listen_to_events( Plugin $container ) {
		$container->on( 'shutdown', [ 'helpers.request', 'finalize_request' ], Plugin::LATE_EVENT );
	}

	/**
	 * @param  Plugin $container
	 * @return void
	 */
	protected function register_api_rewrites( Plugin $container ) {
		$container
			->on( 'query_vars', [ 'helpers.api', 'register_query_vars' ] )
			->on( 'rewrite_rules_array', [ 'helpers.api', 'register_rewrites' ] )
			->on( 'template_redirect', [ 'helpers.api', 'serve_json' ] );
	}
}
