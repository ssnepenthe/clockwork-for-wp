<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Cli_Data_Collection\Command_Context;
use ToyWpEventManagement\EventManagerInterface;
use ToyWpEventManagement\Priority;
use ToyWpEventManagement\SubscriberInterface;

final class Clockwork_Subscriber implements SubscriberInterface {
	private $support;

	public function __construct( Clockwork_Support $support ) {
		$this->support = $support;
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

		if ( $this->support->is_collecting_client_metrics() ) {
			\wp_enqueue_script( 'clockwork-metrics' );
		}

		if ( $this->support->is_toolbar_enabled() ) {
			\wp_enqueue_script( 'clockwork-toolbar' );
		}
	}

	public function finalize_command( Clockwork $clockwork, EventManagerInterface $event_manager ): void {
		$command = Command_Context::current();

		if (
			! $command instanceof Command_Context
			|| $this->support->is_command_filtered( $command->name() )
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
				$this->support->config( 'wp_cli.collect_output', false ) ? $command->output() : ''
			)
			->storeRequest();
	}

	public function finalize_request( Clockwork $clockwork, EventManagerInterface $event_manager ): void {
		$event_manager->trigger( 'cfw_pre_resolve' ); // @todo pass $clockwork? $container?

		$clockwork
			->resolveRequest()
			->storeRequest();
	}

	public function getSubscribedEvents(): array {
		$events = [
			'wp_enqueue_scripts' => 'enqueue_scripts',
		];

		if (
			// @todo Redundant conditions?
			( $this->support->is_enabled() && $this->support->is_recording() )
			&& $this->support->is_collecting_requests()
		) {
			// wp_loaded fires on frontend but also login, admin, etc.
			$events['wp_loaded'] = [ 'initialize_request', Priority::LATE ];
		}

		// @todo Redundant conditions?
		if ( $this->support->is_recording() ) {
			if ( $this->support->is_collecting_commands() ) {
				$events['shutdown'] = [ 'finalize_command', Priority::LATE ];
			} elseif ( $this->support->is_collecting_requests() ) {
				$events['shutdown'] = [ 'finalize_request', Priority::LATE ];
			}
		}

		return $events;
	}

	public function initialize_request( Request $request ): void {
		if ( \headers_sent() ) {
			return;
		}

		// @todo Any reason to suppress errors?
		// @todo Use wp_headers filter of send_headers action? See WP::send_headers().
		\header( 'X-Clockwork-Id: ' . $request->id );
		\header( 'X-Clockwork-Version: ' . Clockwork::VERSION );

		// @todo Set clockwork path header?

		$extra_headers = $this->support->config( 'headers' );

		foreach ( $extra_headers as $header_name => $header_value ) {
			\header( "X-Clockwork-Header-{$header_name}: {$header_value}" );
		}

		// @todo Set subrequest headers?

		if (
			$this->support->is_collecting_client_metrics()
			|| $this->support->is_toolbar_enabled()
		) {
			$cookie = \json_encode(
				[
					'requestId' => $request->id,
					'version' => Clockwork::VERSION,
					'path' => '/__clockwork/',
					'webPath' => $this->support->is_web_installed() ? '/__clockwork' : '/__clockwork/app',
					'token' => $request->updateToken,
					'metrics' => $this->support->is_collecting_client_metrics(),
					'toolbar' => $this->support->is_toolbar_enabled(),
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
