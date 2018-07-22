<?php

namespace Clockwork_For_Wp;

use Pimple\Container;
use Clockwork\Clockwork;
use Clockwork\Storage\FileStorage;
use Clockwork\DataSource\PhpDataSource;
use Pimple\ServiceProviderInterface as Provider;

class Plugin_Provider implements Provider, Bootable_Provider {
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

	public function register( Container $container ) {
		$container['config'] = function( Container $c ) {
			return new Config( apply_filters( 'cfw_config', [
				'enabled' => false, // bool
				'collect_data_always' => true, // bool
				'filter' => [ 'cache' ], // array of strings
				'filtered_uris' => [ // array of strings
					'yolo',
					'fomo',
				],
				'headers' => [ // array of string key => string value
					'holler' => 'baller',
				],
				'server_timing' => 20, // int
				'storage_expiration' => 60 * 24 * 7, // int
				'storage_files_path' => WP_CONTENT_DIR . '/cfw-data', // string
			] ) );
		};

		$container['clockwork'] = function( Container $c ) {
			$clockwork = new Clockwork();

			$clockwork
				->addDataSource( new PhpDataSource() )
				->setStorage( $c['clockwork.storage'] );

			return $clockwork;
		};

		$container['clockwork.storage'] = function( Container $c ) {
			// @todo Move params to config.
			$storage = new FileStorage(
				$c['config']->get_storage_files_path(),
				0700,
				$c['config']->get_storage_expiration()
			);

			$storage->filter = $c['config']->get_filter();

			return $storage;
		};

		$container['helpers.api'] = function( Container $c ) {
			return new Api_Helper( $c['clockwork.storage'] );
		};

		$container['helpers.request'] = function( Container $c ) {
			return new Request_Helper( $c['clockwork'], $c['config'] );
		};
	}

	protected function listen_to_events( Plugin $container ) {
		$container->on( 'shutdown', [ 'helpers.request', 'finalize_request' ], Plugin::LATE_EVENT );
	}

	protected function register_api_rewrites( Plugin $container ) {
		$container
			->on( 'query_vars', [ 'helpers.api', 'register_query_vars' ] )
			->on( 'rewrite_rules_array', [ 'helpers.api', 'register_rewrites' ] )
			->on( 'template_redirect', [ 'helpers.api', 'serve_json' ] );
	}
}
