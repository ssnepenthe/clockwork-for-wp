<?php

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Wp_Cli\Command_Context;

class Clockwork_Subscriber implements Subscriber {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function get_subscribed_events() : array {
		$events = [];

		if (
			// @todo Redundant conditions?
			( $this->plugin->is_enabled() && $this->plugin->is_recording() )
			&& $this->plugin->is_collecting_requests()
		) {
			// wp_loaded fires on frontend but also login, admin, etc.
			$events['wp_loaded'] = [ 'send_headers', Event_Manager::LATE_EVENT ];
		}

		// @todo Redundant conditions?
		if ( $this->plugin->is_recording() ) {
			if ( $this->plugin->is_collecting_commands() ) {
				$events['shutdown'] = [ 'finalize_command', Event_Manager::LATE_EVENT ];
			} else if ( $this->plugin->is_collecting_requests() ) {
				$events['shutdown'] = [ 'finalize_request', Event_Manager::LATE_EVENT ];

			}
		}

		return $events;
	}

	public function finalize_request( Clockwork $clockwork, Event_Manager $event_manager ) {
		$event_manager->trigger( 'cfw_pre_resolve' ); // @todo pass $clockwork? $container?

		$clockwork
			->resolveRequest()
			->storeRequest();
	}

	public function finalize_command(  Clockwork $clockwork, Event_Manager $event_manager ) {
		$command = Command_Context::current();

		if (
			! $command instanceof Command_Context
			|| $this->plugin->is_command_filtered( $command->name() )
		) {
			return;
		}

		$event_manager->trigger( 'cfw_pre_resolve' ); // @todo pass $clockwork? $container?

		$clockwork
			->resolveAsCommand(
				$command->name(),
				$exit_code = null,
				$command->arguments(),
				$command->options(),
				$command->default_arguments(), // @todo Only defaults that aren't set by user???
				$command->default_options(), // @todo Only defaults that aren't set by user???
				$this->plugin->config( 'wp_cli.collect_output', false ) ? $command->output() : ''
			)
			->storeRequest();
	}

	public function send_headers( Request $request ) {
		if ( headers_sent() ) {
			return;
		}

		// @todo Any reason to suppress errors?
		// @todo Use wp_headers filter of send_headers action? See WP::send_headers().
		header( 'X-Clockwork-Id: ' . $request->id );
		header( 'X-Clockwork-Version: ' . Clockwork::VERSION );

		// @todo Set clockwork path header?

		$extra_headers = $this->plugin->config( 'headers' );

		foreach ( $extra_headers as $header_name => $header_value ) {
			header( "X-Clockwork-Header-{$header_name}: {$header_value}" );
		}

		// @todo Set subrequest headers?
	}
}
