<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use Clockwork_For_Wp\Base_Provider;
use Invoker\Invoker;
use Pimple\Container;

/**
 * @internal
 */
final class Wp_Cli_Provider extends Base_Provider {
	public function boot(): void {
		$this->plugin->get_container()->get( Command_Registry::class )->initialize();
	}

	public function register(): void {
		$this->plugin->get_pimple()[ Command_Registry::class ] = static function ( Container $pimple ) {
			return new Command_Registry( $pimple[ Invoker::class ] );
		};
	}

	public function registered(): void {
		if ( ! ( \defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		$this->plugin->get_container()->get( Command_Registry::class )->namespace(
			'clockwork',
			'Manages the Clockwork for WP plugin.',
			static function ( Command_Registry $scoped_registry ): void {
				$scoped_registry
					->add( new Clean_Command() )
					->add( new Generate_Command_List_Command() );
			}
		);
	}
}
