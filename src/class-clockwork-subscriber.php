<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork_For_Wp\Cli_Data_Collection\Command_Context;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

final class Clockwork_Subscriber implements Subscriber {
	private $clockwork;

	private $config;

	private $events;

	private $is;

	public function __construct( Read_Only_Configuration $config, Is $is, Event_Manager $events, Clockwork $clockwork ) {
		$this->config = $config;
		$this->is = $is;
		$this->events = $events;
		$this->clockwork = $clockwork;
	}

	public function get_subscribed_events(): array {
		if ( $this->is->collecting_data() ) {
			return [
				'wp_loaded' => [ 'on_wp_loaded', Event_Manager::LATE_EVENT ],
				'shutdown' => [ 'on_shutdown', Event_Manager::LATE_EVENT ],
			];
		}

		return [];
	}

	public function on_shutdown(): void {
		if ( $this->is->collecting_requests() ) {
			$this->resolve_request();
		} elseif ( $this->is->collecting_commands() ) {
			$this->resolve_command();
		}
	}

	public function on_wp_loaded(): void {
		if ( \headers_sent() ) {
			return;
		}

		if ( $this->is->enabled() && $this->is->collecting_requests() ) {
			$this->set_clockwork_headers();
		}
	}

	private function resolve_command(): void {
		$command = Command_Context::current();

		if ( ! $command instanceof Command_Context || $this->is->command_filtered( $command->name() ) ) {
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

	private function resolve_request(): void {
		$this->events->trigger( 'cfw_pre_resolve' ); // @todo pass $clockwork? $container?

		$this->clockwork
			->resolveRequest()
			->storeRequest();
	}

	private function set_clockwork_headers(): void {
		// @todo Any reason to suppress errors?
		// @todo Use wp_headers filter of send_headers action? See WP::send_headers().
		\header( 'X-Clockwork-Id: ' . $this->clockwork->request()->id );
		\header( 'X-Clockwork-Version: ' . $this->clockwork::VERSION );

		// @todo Set clockwork path header?

		$extra_headers = $this->config->get( 'headers' );

		foreach ( $extra_headers as $header_name => $header_value ) {
			\header( "X-Clockwork-Header-{$header_name}: {$header_value}" );
		}
	}
}
