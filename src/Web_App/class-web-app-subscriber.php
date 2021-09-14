<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Web_App;

use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Incoming_Request;
use Clockwork_For_Wp\Routing\Route_Collection;

final class Web_App_Subscriber implements Subscriber {
	public function get_subscribed_events(): array {
		return [
			'init' => 'register_routes',
			// @todo Move to redirect canonical?
			'template_redirect' => 'redirect_shortcut',
			'redirect_canonical' => 'prevent_canonical_redirect',
		];
	}

	public function prevent_canonical_redirect( $redirect, $requested ) {
		$clockwork = \home_url( '__clockwork' );

		if ( \mb_substr( $requested, 0, \mb_strlen( $clockwork ) ) === $clockwork ) {
			return $requested;
		}

		return $redirect;
	}

	public function redirect_shortcut( Incoming_Request $request ): void {
		/**
		 * @psalm-suppress InvalidArgument
		 *
		 * @see https://github.com/humanmade/psalm-plugin-wordpress/issues/13
		 */
		if ( \untrailingslashit( $request->uri ) !== \home_url( '__clockwork', 'relative' ) ) {
			return;
		}

		\wp_safe_redirect( \home_url( '__clockwork/app' ) );
		exit;
	}

	public function register_routes( Route_Collection $routes ): void {
		$routes->get(
			'^__clockwork/app$',
			'index.php?app=1&asset=index.html',
			[ Web_App_Controller::class, 'serve_assets' ]
		);
		$routes->get(
			'^__clockwork/(((?:css|img|js)/)?.*\.(?:css|html|js|json|png))$',
			'index.php?app=1&asset=$matches[1]',
			[ Web_App_Controller::class, 'serve_assets' ]
		);
	}
}
