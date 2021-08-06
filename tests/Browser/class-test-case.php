<?php

namespace Clockwork_For_Wp\Tests\Browser;

use Goutte\Client;
use PHPUnit\Framework\TestCase;

// @todo Restore db to default state before each test.
// @todo Configurable base uri.
class Test_Case extends TestCase {
	const PASSWORD = 'nothing-to-see-here-folks';

	protected static $api;

	public static function setUpBeforeClass(): void {
		static::api()->clean_metadata();
	}

	public function setUp(): void {
		if ( ! static::api()->is_available() ) {
			$this->markTestSkipped( 'The test plugin does not appear to be active' );
		}
	}

	public function tearDown(): void {
		static::api()->clean_metadata();
	}

	protected static function api() {
		if ( null === static::$api ) {
			static::$api = new \Clockwork_For_Wp\Tests\Api( static::goutte() );
		}

		return static::$api;
	}

	protected static function goutte(): Client {
		$client = new Client;
		// @todo Set https as well?
		$client->setServerParameter( 'HTTP_HOST', static::http_host() );
		$client->followRedirects( false );

		return $client;
	}

	protected static function http_host(): string {
		// @todo Use .env or similar to define this on the machine running the tests
		return 'one.wordpress.test';
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
