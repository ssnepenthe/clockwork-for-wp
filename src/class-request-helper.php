<?php

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Helpers\ServerTiming;

class Request_Helper {
	/**
	 * @var Clockwork
	 */
	protected $clockwork;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @param Clockwork $clockwork
	 * @param Config    $config
	 */
	public function __construct( Clockwork $clockwork, Config $config ) {
		$this->clockwork = $clockwork;
		$this->config = $config;
	}

	/**
	 * @return void
	 */
	public function finalize_request() {
		if ( ! $this->config->is_collecting_data() || $this->is_request_for_filtered_uri() ) {
			return;
		}

		$this->clockwork->resolveRequest();
        $this->clockwork->storeRequest();
	}

	/**
	 * @return void
	 */
	public function send_headers() {
		if (
			! $this->config->is_collecting_data()
			|| ! $this->config->is_enabled()
			|| headers_sent() // Shouldn't happen.
		) {
			return;
		}

		// @todo Any reason to suppress errors?
		// @todo Request as a direct dependency?
		header( 'X-Clockwork-Id: ' . $this->clockwork->getRequest()->id );
		header( 'X-Clockwork-Version: ' . Clockwork::VERSION );

		// @todo Set clockwork path header?

		foreach ( $this->config->get_headers() as $header_name => $header_value ) {
			header( "X-Clockwork-Header-{$header_name}: {$header_value}" );
		}

		$events_count = $this->config->get_server_timing();

		if ( false !== $events_count ) {
			header( 'Server-Timing: ' . ServerTiming::fromRequest(
				$this->clockwork->getRequest(),
				$events_count
			)->value() );
		}
	}

	/**
	 * @return boolean
	 */
	protected function is_request_for_filtered_uri() {
		// @todo Include default for the unlikely (impossible?) case where request uri is not set?
		$request_uri = $_SERVER['REQUEST_URI'];

		foreach ( $this->config->get_filtered_uris() as $uri ) {
			$regex = '#' . str_replace( '#', '\#', $uri ) . '#';

			if ( preg_match( $regex, $request_uri ) ) {
				return true;
			}
		}

		return false;
	}
}
