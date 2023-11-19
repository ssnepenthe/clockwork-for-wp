<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Request\Request;
use WpEventDispatcher\SubscriberInterface;

final class Toolbar_Subscriber implements SubscriberInterface {
	private const COOKIE_NAME = 'x-clockwork';

	private const LOGIN_STYLES = <<<'CSS'
		.login .clockwork-toolbar .success {
			border-left: none;
			box-shadow: none;
			margin: 0;
			padding: 0;
			word-wrap: unset;
		}
	CSS;

	private Is $is;

	private Request $request;

	private string $scripts_dir_url;

	private string $scripts_suffix;

	public function __construct( Is $is, Request $request, string $scripts_dir_url, bool $minified_scripts = false ) {
		$this->is = $is;
		$this->request = $request;
		$this->scripts_dir_url = $scripts_dir_url;
		$this->scripts_suffix = $minified_scripts ? '.min' : '';
	}

	public function getSubscribedEvents(): array {
		return [
			'admin_enqueue_scripts' => 'on_enqueue_scripts',
			'login_enqueue_scripts' => 'on_enqueue_scripts',
			'wp_enqueue_scripts' => 'on_enqueue_scripts',
			'wp_loaded' => 'on_wp_loaded',
		];
	}

	public function on_enqueue_scripts(): void {
		\wp_register_script(
			'clockwork-metrics',
			"{$this->scripts_dir_url}js/dist/metrics{$this->scripts_suffix}.js",
			[],
			'1.0.0',
			true
		);

		\wp_register_script(
			'clockwork-toolbar',
			"{$this->scripts_dir_url}js/dist/toolbar{$this->scripts_suffix}.js",
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
			\wp_add_inline_style( 'login', self::LOGIN_STYLES );
			\wp_enqueue_script( 'clockwork-toolbar' );
		}
	}

	public function on_wp_loaded(): void {
		if ( ! $this->guard() ) {
			$this->unset_cookie();

			return;
		}

		if ( ! ( $this->is->collecting_client_metrics() || $this->is->toolbar_enabled() ) ) {
			$this->unset_cookie();

			return;
		}

		$this->set_cookie();
	}

	private function call_setcookie( $value, $expires ): void {
		$domain = COOKIE_DOMAIN ?: '';
		$secure = \is_ssl() && 'https' === \parse_url( \home_url(), \PHP_URL_SCHEME );

		\setcookie( self::COOKIE_NAME, $value, [
			'expires' => $expires,
			'path' => COOKIEPATH,
			'domain' => $domain,
			'secure' => $secure,
			'httponly' => false,
			'samesite' => 'Lax',
		] );
	}

	private function guard(): bool {
		// @todo When web view is disabled and toolbar is enabled, the "show details" link will 404.
		// @see https://github.com/underground-works/clockwork-browser/issues/4
		return $this->is->enabled() && $this->is->collecting_requests();
	}

	private function set_cookie(): void {
		$data = [
			'requestId' => $this->request->id,
			'version' => Clockwork::VERSION,
			'path' => '/__clockwork/',
			'webPath' => $this->is->web_installed() ? '/__clockwork' : '/__clockwork/app',
			'token' => $this->request->updateToken,
			'metrics' => $this->is->collecting_client_metrics(),
			'toolbar' => $this->is->toolbar_enabled(),
		];

		$this->call_setcookie( \json_encode( $data ), \time() + 60 );
	}

	private function unset_cookie(): void {
		// Reference implementation doesn't unset cookie but I think it is preferable...
		// @see https://github.com/itsgoingd/clockwork/issues/657
		if ( \array_key_exists( self::COOKIE_NAME, $_COOKIE ) ) {
			$this->call_setcookie( '', \time() - 3600 );
			unset( $_COOKIE[ self::COOKIE_NAME ] );
		}
	}
}
