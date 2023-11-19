<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Web_App;

use Clockwork_For_Wp\Incoming_Request;
use Clockwork_For_Wp\Routing\Route_Collection;
use WpEventDispatcher\SubscriberInterface;

final class Web_App_Subscriber implements SubscriberInterface {
	private $request;

	private $routes;

	public function __construct( Incoming_Request $request, Route_Collection $routes ) {
		$this->request = $request;
		$this->routes = $routes;
	}

	public function getSubscribedEvents(): array {
		return [
			'init' => 'register_routes',
			// @todo Move to redirect canonical?
			'template_redirect' => 'redirect_shortcut',
		];
	}

	public function redirect_shortcut(): void {
		/**
		 * @psalm-suppress InvalidArgument
		 *
		 * @see https://github.com/humanmade/psalm-plugin-wordpress/issues/13
		 */
		if ( \untrailingslashit( $this->request->uri ) !== \home_url( '__clockwork', 'relative' ) ) {
			return;
		}

		\wp_safe_redirect( \home_url( '__clockwork/app' ) );
		exit;
	}

	public function register_routes(): void {
		$this->routes->get(
			'^__clockwork/app$',
			'index.php?app=1&asset=index.html',
			[ Web_App_Controller::class, 'serve_assets' ]
		);
		$this->routes->get(
			'^__clockwork/(((?:css|img|js)/)?.*\.(?:css|html|js|json|png))$',
			'index.php?app=1&asset=$matches[1]',
			[ Web_App_Controller::class, 'serve_assets' ]
		);
	}
}
