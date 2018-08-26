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
}
