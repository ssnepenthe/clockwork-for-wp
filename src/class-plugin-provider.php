<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use League\Config\Configuration;
use League\Config\ConfigurationBuilderInterface;
use League\Config\ConfigurationInterface;
use Pimple\Container;

/**
 * @internal
 */
final class Plugin_Provider extends Base_Provider {
	public function boot( Plugin $plugin ): void {
		if ( $plugin->is()->enabled() || $plugin->is()->web_enabled() || $plugin->is()->web_installed() ) {
			$plugin->get_pimple()[ Event_Manager::class ]->attach( new Plugin_Subscriber() );
		}
	}

	public function register( Plugin $plugin ): void {
		require_once __DIR__ . '/plugin-helpers.php';

		$pimple = $plugin->get_pimple();

		$pimple[ ConfigurationBuilderInterface::class ] = static function ( Container $pimple ) {
			$schema = include \dirname( __DIR__ ) . '/config/schema.php';
			$defaults = include \dirname( __DIR__ ) . '/config/defaults.php';

			$config = new Configuration( $schema );

			$config->merge( $defaults );

			$pimple[ Event_Manager::class ]->trigger(
				'cfw_config_init',
				new Private_Schema_Configuration( $config )
			);

			return $config;
		};

		$pimple[ ConfigurationInterface::class ] = static function ( Container $pimple ) {
			return $pimple[ ConfigurationBuilderInterface::class ]->reader();
		};

		$pimple[ Metadata::class ] = static function ( Container $pimple ) {
			return new Metadata(
				$pimple[ Clockwork_Support::class ],
				$pimple[ Clockwork::class ]->storage()
			);
		};
	}
}
