<?php

namespace Clockwork_For_Wp\Web_App;

use Clockwork\Web\Web;

class Web_App_Controller {
	protected $web_helper;
	protected $wp_query;

	public function __construct( Web $web_helper, \WP_Query $wp_query ) {
		$this->web_helper = $web_helper;
		$this->wp_query = $wp_query;
	}

	public function serve_assets( $cfw_asset ) {
		// @todo No WP dependencies in here...
		$asset = untrailingslashit( $cfw_asset );
		$file = $this->web_helper->asset( $asset );

		if ( is_array( $file ) && isset( $file['path'] ) && is_file( $file['path'] ) ) {
			$this->serve_file( $file );
		} else {
			$this->trigger_not_found();
		}
	}

	protected function serve_file( $file ) {
		$size = filesize( $file['path'] );

		// @todo Are any other headers necessary?
		// @todo Use wp_headers filter or send_headers action? Might not be possible using die.
		header( "Content-Type: {$file['mime']}" );
		header( "Content-Length: {$size}" );

		readfile( $file['path'] );
		die;
	}

	protected function trigger_not_found() {
		// @todo Should we handle 404 earlier than template_redirect? Can we make the wp class handle it for us?
		$this->wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
