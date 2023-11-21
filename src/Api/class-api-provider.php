<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Api;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Is;
use Clockwork_For_Wp\Metadata;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Routing\Route_Loader;
use Pimple\Container;

/**
 * @internal
 */
final class Api_Provider extends Base_Provider {
	public function boot( Plugin $plugin ): void {
		$plugin->get_pimple()[ Route_Loader::class ]->load( __DIR__ . '/routes.php' );
	}

	public function register( Plugin $plugin ): void {
		$plugin->get_pimple()[ Api_Controller::class ] = static function ( Container $pimple ) {
			return new Api_Controller(
				$pimple[ AuthenticatorInterface::class ],
				$pimple[ Metadata::class ],
				$pimple[ IncomingRequest::class ],
				$pimple[ Is::class ]
			);
		};
	}
}
