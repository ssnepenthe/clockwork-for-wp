<?php

namespace Clockwork_For_Wp\Tests\Browser;

use Goutte\Client;
use PHPUnit\Framework\TestCase;

class Test_Case extends TestCase {
	const PASSWORD = 'nothing-to-see-here-folks';

	protected static $api;
	protected static $http_host;
	protected static $https;

	public static function setUpBeforeClass(): void {
		if ( static::api()->is_available() ) {
			static::api()->clean_metadata();
		}
	}

	public function setUp(): void {
		if ( ! static::api()->is_available() ) {
			$this->markTestSkipped( 'The test plugin does not appear to be active' );
		}
	}

	public function tearDown(): void {
		if ( static::api()->is_available() ) {
			static::api()->clean_metadata();
		}
	}

	protected static function api() {
		if ( null === static::$api ) {
			static::$api = new \Clockwork_For_Wp\Tests\Api( static::goutte() );
		}

		return static::$api;
	}

	protected static function goutte(): Client {
		$client = new Client;

		$client->setServerParameter( 'HTTP_HOST', static::http_host() );
		$client->setServerParameter( 'HTTPS', static::https() );
		$client->followRedirects( false );

		return $client;
	}

	protected static function http_host(): string {
		if ( \is_string( static::$http_host ) ) {
			return static::$http_host;
		}

		static::maybe_load_base_uri();

		if ( ! \is_string( static::$http_host ) ) {
			static::$http_host = 'one.wordpress.test';
		}

		return static::$http_host;
	}

	protected static function https(): bool {
		if ( \is_bool( static::$https ) ) {
			return static::$https;
		}

		static::maybe_load_base_uri();

		if ( ! \is_bool( static::$https ) ) {
			static::$https = false;
		}

		return static::$https;
	}

	protected static function maybe_load_base_uri() {
		$has_host = \is_string( static::$http_host );
		$has_https = \is_bool( static::$https );

		if ( $has_host && $has_https ) {
			return;
		}

		if ( ! \is_readable( __DIR__ . '/../baseuri' ) ) {
			return;
		}

		$base_uri = \trim( \file_get_contents( __DIR__ . '/../baseuri' ) );
		$parsed = \parse_url( $base_uri );

		if ( ! $has_host ) {
			if ( ! array_key_exists( 'host', $parsed ) ) {
				throw new \InvalidArgumentException( '@todo' );
			}

			static::$http_host = $parsed['host'];
		}

		if ( ! $has_https ) {
			if ( ! $has_https && ! array_key_exists( 'scheme', $parsed ) ) {
				throw new \InvalidArgumentException( '@todo' );
			}

			static::$https = 'https' === $parsed['scheme'] ? true : false;
		}
	}

	protected function test_config(): array {
		return [];
	}

	public function request(
		string $method,
		string $uri,
		array $parameters = [],
		array $files = [],
		array $server = [],
		string $content = null,
		bool $changeHistory = true
	) : Response {
		if ( ! empty( $config = $this->test_config() ) ) {
			$uri .= '?' . http_build_query( $config );
		}

		$client = static::goutte();

		$client->request(
			$method,
			$uri,
			$parameters,
			$files,
			$server,
			$content,
			$changeHistory
		);

		return new Response( $client );
	}

	public function get(
		string $uri,
		array $parameters = [],
		array $files = [],
		array $server = [],
		string $content = null,
		bool $changeHistory = true
	) : Response {
		return $this->request(
			'GET',
			$uri,
			$parameters,
			$files,
			$server,
			$content,
			$changeHistory
		);
	}

	public function post(
		string $uri,
		array $parameters = [],
		array $files = [],
		array $server = [],
		string $content = null,
		bool $changeHistory = true
	) : Response {
		return $this->request(
			'POST',
			$uri,
			$parameters,
			$files,
			$server,
			$content,
			$changeHistory
		);
	}

	public function post_json( string $uri, $data ) : Response {
		$content = is_string( $data ) ? $data : json_encode( $data );

		return $this->request( 'POST', $uri, [], [], [
			'Accept' => 'application/json',
			'CONTENT_LENGTH' => mb_strlen( $content, '8bit' ),
			'CONTENT_TYPE' => 'application/json',
		], $content );
	}
}
