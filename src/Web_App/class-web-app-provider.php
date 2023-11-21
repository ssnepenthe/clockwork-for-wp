<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Web_App;

use Clockwork\Web\Web;
use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Is;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Routing\Route_Loader;
use Pimple\Container;

/**
 * @internal
 */
final class Web_App_Provider extends Base_Provider {
	public function boot( Plugin $plugin ): void {
		$plugin->get_pimple()[ Route_Loader::class ]->load( __DIR__ . '/routes.php' );
	}

	public function register( Plugin $plugin ): void {
		$plugin->get_pimple()[ Web_App_Controller::class ] = static function ( Container $pimple ) {
			return new Web_App_Controller( new Web(), $pimple[ Is::class ] );
		};
	}
}
