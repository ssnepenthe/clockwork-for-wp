<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Web_App;

use Clockwork\Web\Web;
use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Incoming_Request;
use Clockwork_For_Wp\Routing\Route_Collection;
use Pimple\Container;
use WP_Query;

/**
 * @internal
 */
final class Web_App_Provider extends Base_Provider {
	public function boot(): void {
		if ( $this->plugin->is_web_enabled() && ! $this->plugin->is_web_installed() ) {
			parent::boot();
		}
	}

	public function register(): void {
		$pimple = $this->plugin->get_pimple();

		$pimple[ Web_App_Controller::class ] = static function ( Container $pimple ) {
			return new Web_App_Controller( new Web(), $pimple[ WP_Query::class ] );
		};

		$pimple[ Web_App_Subscriber::class ] = static function ( Container $pimple ) {
			return new Web_App_Subscriber( $pimple[ Incoming_Request::class ], $pimple[ Route_Collection::class ] );
		};
	}

	protected function subscribers(): array {
		return [ Web_App_Subscriber::class ];
	}
}
