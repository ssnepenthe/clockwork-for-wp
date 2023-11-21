<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Web_App;

use Clockwork\Web\Web;
use Clockwork_For_Wp\Is;
use Clockwork_For_Wp\Routing\File_Responder;
use SimpleWpRouting\Exception\NotFoundHttpException;
use SimpleWpRouting\Responder\RedirectResponder;

final class Web_App_Controller {
	private $is;

	private $web_helper;

	public function __construct( Web $web_helper, Is $is ) {
		$this->web_helper = $web_helper;
		$this->is = $is;
	}

	public function redirect(): RedirectResponder {
		$this->ensure_clockwork_is_enabled();

		// @todo home_url() ?
		return new RedirectResponder( '/__clockwork/app' );
	}

	public function serve_assets( array $params ): File_Responder {
		$this->ensure_clockwork_is_enabled();

		$path = \rtrim( $params['path'] ?? 'index.html', '/\\' );
		$file = $this->web_helper->asset( $path );

		if ( ! ( \is_array( $file ) && isset( $file['path'] ) && \is_file( $file['path'] ) ) ) {
			throw new NotFoundHttpException();
		}

		return new File_Responder( $file['path'], $file['mime'] );
	}

	private function ensure_clockwork_is_enabled(): void {
		if ( ! ( $this->is->web_enabled() && ! $this->is->web_installed() ) ) {
			throw new NotFoundHttpException();
		}
	}
}
