<?php

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;

class Request_Helper {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * @return void
	 */
	public function finalize_request() {
		// @todo Include default for the case where request uri is not set?
		if ( $this->plugin->is_uri_filtered( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$this->plugin->service( 'clockwork' )->resolveRequest();
		$this->plugin->service( 'clockwork' )->storeRequest();
	}

	/**
	 * @return void
	 */
	public function send_headers() {
		if (
			$this->plugin->is_uri_filtered( $_SERVER['REQUEST_URI'] ) // @todo See above.
			|| ! $this->plugin->is_enabled() // @todo Move to definition class.
			|| headers_sent() // Shouldn't happen.
		) {
			return;
		}

		// @todo Any reason to suppress errors?
		// @todo Request as a direct dependency?
		header( 'X-Clockwork-Id: ' . $this->plugin->service( 'clockwork' )->getRequest()->id );
		header( 'X-Clockwork-Version: ' . Clockwork::VERSION );

		// @todo Set clockwork path header?

		$extra_headers = $this->plugin->service( 'config' )->get_headers();

		foreach ( $extra_headers as $header_name => $header_value ) {
			header( "X-Clockwork-Header-{$header_name}: {$header_value}" );
		}

		// @todo Set subrequest headers?
	}
}
