<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use Clockwork_For_Wp\Base_Provider;
use Invoker\Invoker;

/**
 * @internal
 */
final class Wp_Cli_Provider extends Base_Provider {
	public function boot(): void {
		$this->plugin[ Command_Registry::class ]->initialize();
	}

	public function register(): void {
		$this->plugin[ Command_Registry::class ] = function () {
			return new Command_Registry( $this->plugin[ Invoker::class ] );
		};
	}

	public function registered(): void {
		if ( ! ( \defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		$this->plugin[ Command_Registry::class ]->namespace(
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
