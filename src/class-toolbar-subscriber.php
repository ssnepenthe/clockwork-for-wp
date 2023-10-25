<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Subscriber;

final class Toolbar_Subscriber implements Subscriber {
	private Is $is;

	private Request $request;

	public function __construct( Is $is, Request $request ) {
		$this->is = $is;
		$this->request = $request;
	}

	public function get_subscribed_events(): array {
		return [
			'wp_enqueue_scripts' => 'on_wp_enqueue_scripts',
			'wp_loaded' => 'on_wp_loaded',
		];
	}

	public function on_wp_enqueue_scripts(): void {
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

		if ( ! $this->guard() ) {
			return;
		}

		if ( $this->is->collecting_client_metrics() ) {
			\wp_enqueue_script( 'clockwork-metrics' );
		}

		if ( $this->is->toolbar_enabled() ) {
			\wp_enqueue_script( 'clockwork-toolbar' );
		}
	}

	public function on_wp_loaded(): void {
		// @todo unset cookie if currently set but toolbar disabled.
		if ( ! $this->guard() ) {
			return;
		}

		if ( ! ( $this->is->collecting_client_metrics() || $this->is->toolbar_enabled() ) ) {
			return;
		}

		$cookie = \json_encode(
			[
				'requestId' => $this->request->id,
				'version' => Clockwork::VERSION,
				'path' => '/__clockwork/',
				'webPath' => $this->is->web_installed() ? '/__clockwork' : '/__clockwork/app',
				'token' => $this->request->updateToken,
				'metrics' => $this->is->collecting_client_metrics(),
				'toolbar' => $this->is->toolbar_enabled(),
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

	private function guard(): bool {
		// @todo When web view is disabled and toolbar is enabled, the "show details" link will 404.
		// @see https://github.com/underground-works/clockwork-browser/issues/4
		return $this->is->enabled() && $this->is->collecting_requests();
	}
}
