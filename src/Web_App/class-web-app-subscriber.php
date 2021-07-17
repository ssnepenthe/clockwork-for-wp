<?php

namespace Clockwork_For_Wp\Web_App;

use Clockwork_For_Wp\Event_Management\Managed_Subscriber;
use Clockwork_For_Wp\Routing\Route_Collection;

class Web_App_Subscriber implements Managed_Subscriber {
	public function get_subscribed_events() : array {
		return [
			'init' => 'register_routes',
			// @todo Move to redirect canonical?
			'template_redirect' => 'redirect_shortcut',
			'redirect_canonical' => 'prevent_canonical_redirect',
		];
	}

	public function register_routes( Route_Collection $routes ) {
		$routes->get(
			'__clockwork/app',
			'index.php?cfw_app=1&cfw_asset=index.html',
			[ Web_App_Controller::class, 'serve_assets' ]
		);
		$routes->get(
			'__clockwork/(.*)',
			'index.php?cfw_app=1&cfw_asset=$matches[1]',
			[ Web_App_Controller::class, 'serve_assets' ]
		);
	}

	public function redirect_shortcut() {
		$clockwork = home_url( '__clockwork', 'relative' );

		if (
			! isset( $_SERVER['REQUEST_URI'] )
			|| $clockwork !== untrailingslashit( $_SERVER['REQUEST_URI'] )
		) {
			return;
		}

		wp_safe_redirect( home_url( '__clockwork/app' ) );
		die;
	}

	public function prevent_canonical_redirect( $redirect, $requested ) {
		$clockwork = home_url( '__clockwork' );

		if ( $clockwork === substr( $requested, 0, strlen( $clockwork ) ) ) {
			return $requested;
		}

		return $redirect;
	}
}
