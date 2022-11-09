<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Web_App;

use Clockwork\Web\Web;
use Clockwork_For_Wp\Plugin;
use Daedalus\Routing\Responder\FileResponder;
use ToyWpRouting\Exception\NotFoundHttpException;
use ToyWpRouting\Responder\RedirectResponder;

final class Web_App_Controller {
	private $web_helper;
	private $plugin;

	public function __construct( Web $web_helper, Plugin $plugin ) {
		$this->web_helper = $web_helper;
		$this->plugin = $plugin;
	}

	public function serve_asset( $asset ) {
		$this->assert_clockwork_is_enabled();

		$asset = \rtrim( $asset, '/\\' );

		$file = $this->get_asset( $asset );

		return new FileResponder( $file['path'], $file['mime'] );
	}

	public function serve_index() {
		$this->assert_clockwork_is_enabled();

		return $this->serve_asset( 'index.html' );
	}

	public function serve_redirect() {
		$this->assert_clockwork_is_enabled();

		return new RedirectResponder( \home_url( '__clockwork/app' ) );
	}

	private function get_asset( $asset ) {
		$asset = \rtrim( $asset, '/\\' );
		$file = $this->web_helper->asset( $asset );

		if (
			! is_array( $file )
			|| ! isset( $file['path'] )
			|| ! is_file( $file['path'] )
			|| ! isset( $file['mime'] )
		) {
			throw new NotFoundHttpException();
		}

		return $file;
	}

	private function assert_clockwork_is_enabled(): void {
		// @todo might need to rethink - we should never hit this if web installed...
		if (
			! $this->plugin->is_web_enabled()
			|| $this->plugin->is_web_installed()
		) {
			throw new NotFoundHttpException();
		}
	}
}
