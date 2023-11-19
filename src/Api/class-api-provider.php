<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Api;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Metadata;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Routing\Route_Collection;
use Pimple\Container;
use WpEventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class Api_Provider extends Base_Provider {
	public function boot( Plugin $plugin ): void {
		if ( $plugin->is()->enabled() ) {
			$pimple = $plugin->get_pimple();

			$pimple[ EventDispatcherInterface::class ]->addSubscriber(
				new Api_Subscriber( $plugin->is(), $pimple[ Route_Collection::class ] )
			);
		}
	}

	public function register( Plugin $plugin ): void {
		$pimple = $plugin->get_pimple();

		$pimple[ Api_Controller::class ] = static function ( Container $pimple ) {
			return new Api_Controller(
				$pimple[ AuthenticatorInterface::class ],
				$pimple[ Metadata::class ],
				$pimple[ IncomingRequest::class ]
			);
		};
	}
}
