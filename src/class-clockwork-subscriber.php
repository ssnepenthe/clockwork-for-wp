<?php

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

class Clockwork_Subscriber implements Subscriber {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function get_subscribed_events() : array {
		$events = [];

		if (
			(
				$this->plugin->is_enabled()
				&& $this->plugin->is_recording( $this->plugin[ Clockwork::class ]->request() )
			)
			&& $this->plugin->is_collecting_requests()
		) {
			// wp_loaded fires on frontend but also login, admin, etc.
			$events['wp_loaded'] = [ 'send_headers', Event_Manager::LATE_EVENT ];
		}

		if (
			$this->plugin->is_collecting_requests()
			&& $this->plugin->is_recording( $this->plugin[ Clockwork::class ]->request() )
		) {
			$events['shutdown'] = [ 'finalize_request', Event_Manager::LATE_EVENT ];
		}

		return $events;
	}

	public function finalize_request( Clockwork $clockwork, Event_Manager $event_manager ) {
		$event_manager->trigger( 'cfw_pre_resolve' ); // @todo pass $clockwork? $container?

		$clockwork->resolveRequest();
		$clockwork->storeRequest();
	}

	public function send_headers( Clockwork $clockwork ) {
		// @todo Include default for the case where request uri is not set?
		if ( headers_sent() ) {
			return;
		}

		// @todo Any reason to suppress errors?
		// @todo Request as a direct dependency?
		// @todo Use wp_headers filter of send_headers action? See WP::send_headers().
		header( 'X-Clockwork-Id: ' . $clockwork->getRequest()->id );
		header( 'X-Clockwork-Version: ' . $clockwork::VERSION );

		// @todo Set clockwork path header?

		$extra_headers = $this->plugin->config( 'headers' );

		foreach ( $extra_headers as $header_name => $header_value ) {
			header( "X-Clockwork-Header-{$header_name}: {$header_value}" );
		}

		// @todo Set subrequest headers?
	}
}
