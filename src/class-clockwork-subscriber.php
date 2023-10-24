<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Cli_Data_Collection\Command_Context;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

final class Clockwork_Subscriber implements Subscriber {
	private $plugin;

	protected $events;

	protected $clockwork;

	protected $request;

	public function __construct( Plugin $plugin, Event_Manager $events, Clockwork $clockwork, Request $request ) {
		$this->plugin = $plugin;
		$this->events = $events;
		$this->clockwork = $clockwork;
		$this->request = $request;
	}

	public function enqueue_scripts(): void {
		// @todo Should this be implemented as a separate plugin?
		\wp_register_script(
			'clockwork-metrics',
			'https://cdn.jsdelivr.net/gh/underground-works/clockwork-browser@1/dist/metrics.js',
			[],
			'1.0.0',
			true
		);

		\wp_register_script(
			'clockwork-toolbar',
			'https://cdn.jsdelivr.net/gh/underground-works/clockwork-browser@1/dist/toolbar.js',
			[],
			'1.0.0',
			true
		);

		if ( $this->plugin->is()->collecting_client_metrics() ) {
			\wp_enqueue_script( 'clockwork-metrics' );
		}

		if ( $this->plugin->is()->toolbar_enabled() ) {
			\wp_enqueue_script( 'clockwork-toolbar' );
		}
	}

	public function finalize_command(): void {
		$command = Command_Context::current();

		if (
			! $command instanceof Command_Context
			|| $this->plugin->is()->command_filtered( $command->name() )
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
				$this->plugin->config( 'wp_cli.collect_output', false ) ? $command->output() : ''
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
		$events = [
			'wp_enqueue_scripts' => 'enqueue_scripts',
		];

		if (
			// @todo Redundant conditions?
			( $this->plugin->is()->enabled() && $this->plugin->is()->recording() )
			&& $this->plugin->is()->collecting_requests()
		) {
			// wp_loaded fires on frontend but also login, admin, etc.
			$events['wp_loaded'] = [ 'initialize_request', Event_Manager::LATE_EVENT ];
		}

		// @todo Redundant conditions?
		if ( $this->plugin->is()->recording() ) {
			if ( $this->plugin->is()->collecting_commands() ) {
				$events['shutdown'] = [ 'finalize_command', Event_Manager::LATE_EVENT ];
			} elseif ( $this->plugin->is()->collecting_requests() ) {
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

		$extra_headers = $this->plugin->config( 'headers' );

		foreach ( $extra_headers as $header_name => $header_value ) {
			\header( "X-Clockwork-Header-{$header_name}: {$header_value}" );
		}

		// @todo Set subrequest headers?

		if (
			$this->plugin->is()->collecting_client_metrics()
			|| $this->plugin->is()->toolbar_enabled()
		) {
			$cookie = \json_encode(
				[
					'requestId' => $this->request->id,
					'version' => Clockwork::VERSION,
					'path' => '/__clockwork/',
					'webPath' => $this->plugin->is()->web_installed() ? '/__clockwork' : '/__clockwork/app',
					'token' => $this->request->updateToken,
					'metrics' => $this->plugin->is()->collecting_client_metrics(),
					'toolbar' => $this->plugin->is()->toolbar_enabled(),
				]
			);

			\setcookie(
				'x-clockwork',
				$cookie,
				\time() + 60,
				COOKIEPATH,
				COOKIE_DOMAIN ?: '',
				\is_ssl() && 'https' === \parse_url( \home_url(), \PHP_URL_SCHEME ),
				false
			);
		}
	}
}
