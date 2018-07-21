<?php

namespace Clockwork_For_Wp;

use Pimple\Container;
use Clockwork\Clockwork;
use Clockwork\Storage\FileStorage;
use Clockwork\DataSource\PhpDataSource;
use Pimple\ServiceProviderInterface as Provider;

class Plugin_Provider implements Provider, Bootable_Provider {
	public function boot( Plugin $container ) {
		$container
			->on( 'query_vars', [ 'helpers.api', 'register_query_vars' ] )
			->on( 'rewrite_rules_array', [ 'helpers.api', 'register_rewrites' ] )
			->on( 'template_redirect', [ 'helpers.api', 'serve_json' ] )
			->on( 'shutdown', [ 'helpers.request', 'finalize_request' ], Plugin::LATE_EVENT )
			->on( 'template_redirect', [ 'helpers.request', 'send_headers' ] );
	}

	public function register( Container $container ) {
		$container['clockwork'] = function( Container $c ) {
			$clockwork = new Clockwork();

			$clockwork
				->addDataSource( new PhpDataSource() )
				->setStorage( $c['clockwork.storage'] );

			return $clockwork;
		};

		$container['clockwork.storage'] = function( Container $c ) {
			// @todo Move params to config.
			return new FileStorage( WP_CONTENT_DIR . '/cfw-data', 0700, 60 * 24 * 7 );
		};

		$container['helpers.api'] = function( Container $c ) {
			return new Api_Helper( $c['clockwork.storage'] );
		};

		$container['helpers.request'] = function( Container $c ) {
			return new Request_Helper( $c['clockwork'] );
		};
	}
}
