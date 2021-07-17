<?php

namespace Clockwork_For_Wp\Tests\Browser;

use Goutte\Client;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\BrowserKit\Response as Browser_Kit_Response;
use Symfony\Component\DomCrawler\Crawler;

class Response {
	protected $goutte;

	public function __construct( Client $goutte ) {
		$this->goutte = $goutte;
	}

	// @todo Replace usages with Clockwork_For_Wp\array_get().
	protected function dot_get( array $target, string $key, $default = null ) {
		$keys = explode( '.', $key );

		foreach ( $keys as $i => $segment ) {
			unset( $keys[ $i ] );

			if ( null === $segment ) {
				return $target;
			}

			if ( array_key_exists( $segment, $target ) ) {
				$target = $target[ $segment ];
			}
		}

		return $target;
	}

	public function dd() : void {
		dump( $this );
		die;
	}

	public function status() : int {
		return $this->unwrap()->getStatusCode();
	}

	public function header( string $header, bool $first = true ) {
		return $this->unwrap()->getHeader( $header, $first );
	}

	public function crawler() : Crawler {
		return $this->goutte->getCrawler();
	}

	public function unwrap() : Browser_Kit_Response {
		// @todo ->getInternalResponse() more appropriate?
		return $this->goutte->getResponse();
	}

	public function url() : string {
		// @todo ->getInternalRequest() more appropriate?
		return $this->goutte->getRequest()->getUri();
	}

	public function is_redirect( string $location = null ) : bool {
		return in_array($this->status(), [ 201, 301, 302, 303, 307, 308 ], true ) && (
			null === $location ?: $location === $this->header( 'Location' )
		);
	}

	public function follow_redirects() {
		do {
			$this->goutte->followRedirect();
		} while ( $this->is_redirection() );

		return $this;
	}

	// STATUS CHECKS

	public function is_redirection() : bool {
		return $this->status() >= 300 && $this->status() < 400;
	}

	public function is_ok() : bool {
		return 200 === $this->status();
	}

	public function is_forbidden() : bool {
		return 403 === $this->status();
	}

	public function is_not_found() : bool {
		return 404 === $this->status();
	}

	// DOM ASSERTIONS

	public function assert_present( string $selector ) {
		PHPUnit::assertGreaterThan(
			0,
			$this->crawler()->filter( $selector )->count(),
			"No DOM nodes present matching {$selector}"
		);

		return $this;
	}

	// JSON ASSERTIONS

	public function decode_response_json() {
		$decoded = json_decode( $this->unwrap()->getContent(), true );

		if ( null === $decoded || JSON_ERROR_NONE !== json_last_error() ) {
			PHPUnit::fail( 'Response contains invalid JSON' );
		}

		return $decoded;
	}

	public function assert_json( \Closure $callback ) {
		$callback( $this->decode_response_json() );

		return $this;
	}

	public function assert_json_path( string $path, $expected ) {
		if ( $expected instanceof \Closure ) {
			$expected( $this->dot_get( $this->decode_response_json(), $path ) );
		} else {
			// @todo Message?
			PHPUnit::assertSame(
				$expected,
				$this->dot_get( $this->decode_response_json(), $path )
			);
		}


		return $this;
	}

	// HEADER ASSERTIONS

	public function assert_header( string $name, $value = null ) {
		PHPUnit::assertNotNull(
			$actual = $this->unwrap()->getHeader( $name ),
			"Header {$name} not present on response"
		);

		if ( $value instanceof \Closure ) {
			$value( $actual );
		} else if ( ! is_null( $value ) ) {
			PHPUnit::assertEquals(
				$value,
				$actual,
				"Header {$name} was found, but value {$actual} does not match {$value}"
			);
		}

		return $this;
	}

	public function assert_header_missing( string $name ) {
		PHPUnit::assertNull(
			$this->unwrap()->getHeader( $name ),
			"Header {$name} was present on response but should not be"
		);

		return $this;
	}

	public function assert_header_starts_with( string $name, string $prefix ) {
		$this->assert_header( $name );

		PHPUnit::assertStringStartsWith(
			$prefix,
			$actual = $this->unwrap()->getHeader( $name ),
			"Header {$name} was found, but value {$actual} does not start with {$prefix}"
		);

		return $this;
	}

	// REDIRECTION ASSERTIONS

	public function assert_redirect( string $uri = null ) {
		PHPUnit::assertTrue(
			$this->is_redirect(),
			"Response status code {$this->status()} is not a redirect status code"
		);

		// @todo
		// if ( is_string( $uri ) ) {
		// 	$this->assert_location( $uri );
		// }

		return $this;
	}

	// STATUS ASSERTIONS

	public function assert_ok() {
		PHPUnit::assertTrue(
			$this->is_ok(),
			"Response status code {$this->status()} does not match expected 200 status code"
		);

		return $this;
	}

	public function assert_forbidden() {
		PHPUnit::assertTrue(
			$this->is_forbidden(),
			"Response status code {$this->status()} does not match expected 403 status code"
		);

		return $this;
	}

	public function assert_not_found() {
		PHPUnit::assertTrue(
			$this->is_not_found(),
			"Response status code {$this->status()} does not match expected 404 status code"
		);

		return $this;
	}

	// URL ASSERTIONS

	public function assert_url_ends_with( string $suffix ) {
		PHPUnit::assertStringEndsWith(
			$suffix,
			$this->url(),
			"Page URL does not end with {$suffix}"
		);

		return $this;
	}
}
