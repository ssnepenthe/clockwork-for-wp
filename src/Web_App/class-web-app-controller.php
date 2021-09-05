<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Web_App;

use Clockwork\Web\Web;
use WP_Query;

final class Web_App_Controller {
	private $web_helper;
	private $wp_query;

	public function __construct( Web $web_helper, WP_Query $wp_query ) {
		$this->web_helper = $web_helper;
		$this->wp_query = $wp_query;
	}

	public function serve_assets( $asset ): void {
		$asset = \rtrim( $asset, '/\\' );
		$file = $this->web_helper->asset( $asset );

		if ( \is_array( $file ) && isset( $file['path'] ) && \is_file( $file['path'] ) ) {
			$this->serve_file( $file );
		} else {
			$this->trigger_not_found();
		}
	}

	private function serve_file( $file ): void {
		$size = \filesize( $file['path'] );

		// @todo Are any other headers necessary?
		// @todo Use wp_headers filter or send_headers action? Might not be possible using die.
		\header( "Content-Type: {$file['mime']}" );
		\header( "Content-Length: {$size}" );

		\readfile( $file['path'] );
		exit;
	}

	private function trigger_not_found(): void {
		// @todo Should we handle 404 earlier than template_redirect? Can we make the wp class handle it for us?
		$this->wp_query->set_404();
		\status_header( 404 );
		\nocache_headers();
	}
}
