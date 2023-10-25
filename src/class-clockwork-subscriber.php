<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Cli_Data_Collection\Command_Context;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

final class Clockwork_Subscriber implements Subscriber {
	private $config;

	protected $events;

	protected $clockwork;

	private $is;

	protected $request;

	public function __construct(
		Read_Only_Configuration $config,
		Is $is,
		Event_Manager $events,
		Clockwork $clockwork,
		Request $request
	) {
		$this->config = $config;
		$this->is = $is;
		$this->events = $events;
		$this->clockwork = $clockwork;
		$this->request = $request;
	}

	public function finalize_command(): void {
		$command = Command_Context::current();

		if (
			! $command instanceof Command_Context
			|| $this->is->command_filtered( $command->name() )
		) {
			return;
		}

		$this->events->trigger( 'cfw_pre_resolve' ); // @todo pass $clockwork? $container?

		$this->clockwork
			->resolveAsCommand(
				$command->name(),
				$exit_code = null,
				$command->arguments(),
				$command->options(),
				$command->default_arguments(), // @todo Only defaults that aren't set by user???
				$command->default_options(), // @todo Only defaults that aren't set by user???
				$this->config->get( 'wp_cli.collect_output', false ) ? $command->output() : ''
			)
			->storeRequest();
	}

	public function finalize_request(): void {
		$this->events->trigger( 'cfw_pre_resolve' ); // @todo pass $clockwork? $container?

		$this->clockwork
			->resolveRequest()
			->storeRequest();
	}

	public function get_subscribed_events(): array {
		$events = [];

		if (
			// @todo Redundant conditions?
			( $this->is->enabled() && $this->is->recording() )
			&& $this->is->collecting_requests()
		) {
			// wp_loaded fires on frontend but also login, admin, etc.
			$events['wp_loaded'] = [ 'initialize_request', Event_Manager::LATE_EVENT ];
		}

		// @todo Redundant conditions?
		if ( $this->is->recording() ) {
			if ( $this->is->collecting_commands() ) {
				$events['shutdown'] = [ 'finalize_command', Event_Manager::LATE_EVENT ];
			} elseif ( $this->is->collecting_requests() ) {
				$events['shutdown'] = [ 'finalize_request', Event_Manager::LATE_EVENT ];
			}
		}

		return $events;
	}

	public function initialize_request(): void {
		if ( \headers_sent() ) {
			return;
		}

		// @todo Any reason to suppress errors?
		// @todo Use wp_headers filter of send_headers action? See WP::send_headers().
		\header( 'X-Clockwork-Id: ' . $this->request->id );
		\header( 'X-Clockwork-Version: ' . Clockwork::VERSION );

		// @todo Set clockwork path header?

		$extra_headers = $this->config->get( 'headers' );

		foreach ( $extra_headers as $header_name => $header_value ) {
			\header( "X-Clockwork-Header-{$header_name}: {$header_value}" );
		}

		// @todo Set subrequest headers?
	}
}
