<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use League\Config\Configuration as LeagueConfiguration;
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

		$pimple[ Configuration::class ] = static function ( Container $pimple ) {
			$schema = include \dirname( __DIR__ ) . '/config/schema.php';
			$defaults = include \dirname( __DIR__ ) . '/config/defaults.php';

			$config = new Configuration( new LeagueConfiguration( $schema ) );

			$config->merge( $defaults );

			$pimple[ Event_Manager::class ]->trigger( 'cfw_config_init', $config );

			return $config;
		};

		$pimple[ Read_Only_Configuration::class ] = static function ( Container $pimple ) {
			return $pimple[ Configuration::class ]->reader();
		};

		$pimple[ Metadata::class ] = static function ( Container $pimple ) {
			return new Metadata(
				$pimple[ Clockwork_Support::class ],
				$pimple[ Clockwork::class ]->storage()
			);
		};
	}
}
