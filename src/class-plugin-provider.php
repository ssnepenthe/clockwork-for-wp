<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Request\IncomingRequest;
use League\Config\Configuration as LeagueConfiguration;
use Pimple\Container;
use SimpleWpRouting\Support\RequestContext;
use WpEventDispatcher\EventDispatcher;
use WpEventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class Plugin_Provider extends Base_Provider {
	public function boot( Plugin $plugin ): void {
		if ( $plugin->is()->enabled() || $plugin->is()->web_enabled() || $plugin->is()->web_installed() ) {
			$plugin->get_pimple()[ EventDispatcherInterface::class ]->addSubscriber( new Plugin_Subscriber() );
		}
	}

	public function register( Plugin $plugin ): void {
		require_once __DIR__ . '/plugin-helpers.php';
		require_once __DIR__ . '/wordpress-helpers.php';

		$pimple = $plugin->get_pimple();

		$pimple[ Configuration::class ] = static function ( Container $pimple ) {
			$schema = include \dirname( __DIR__ ) . '/config/schema.php';
			$defaults = include \dirname( __DIR__ ) . '/config/defaults.php';

			$config = new Configuration( new LeagueConfiguration( $schema ) );

			$config->merge( $defaults );

			\do_action( 'cfw_config_init', $config );

			return $config;
		};

		$pimple[ EventDispatcherInterface::class ] = static function () {
			return new EventDispatcher();
		};

		$pimple[ Is::class ] = $pimple->factory( static function ( Container $pimple ) {
			return $pimple[ Plugin::class ]->is();
		} );

		$pimple[ Read_Only_Configuration::class ] = static function ( Container $pimple ) {
			return $pimple[ Configuration::class ]->reader();
		};

		$pimple[ Request::class ] = static function ( Container $pimple ) {
			return new Request( $pimple[ IncomingRequest::class ], $pimple[ RequestContext::class ] );
		};

		$pimple[ Metadata::class ] = static function ( Container $pimple ) {
			return new Metadata(
				$pimple[ Clockwork_Support::class ],
				$pimple[ Clockwork::class ]->storage()
			);
		};
	}
}
