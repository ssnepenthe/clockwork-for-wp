<?php

namespace Clockwork_For_Wp\Tests\Browser;

use Clockwork_For_Wp\Tests\Cli;
use Clockwork_For_Wp\Tests\Requires_Plugins;
use Goutte\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

// @todo Restore db to default state before each test.
// @todo Clear cfw-data dir before each test.
class Test_Case extends TestCase {
	use Requires_Plugins;

	protected static $content_url;

	protected static function base_uri() : string {
		return 'http://local.wordpress.test';
	}

	protected static function content_url( string $content_url = null ) : string {
		if ( is_string( $content_url ) ) {
			static::$content_url = $content_url;
		}

		if ( ! is_string( static::$content_url ) ) {
			static::$content_url = trim(
				Cli::wp( 'eval', 'echo WP_CONTENT_URL;' )->mustRun()->getOutput()
			);
		}

		return static::$content_url;
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
		$client = new Client(
			// @todo Doesn't currently work - we will prepend base uri below.
			// @see https://github.com/FriendsOfPHP/Goutte/issues/427
			// HttpClient::create( [
			// 	'base_uri' => static::base_uri(),
			// ] );
		);
		$client->followRedirects( false );

		if ( ! ( 0 === strpos( $uri, 'http://' ) || 0 === strpos( $uri, 'https://' ) ) ) {
			$uri = rtrim( static::base_uri(), '/' ) . '/' . ltrim( $uri, '/' );
		}

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
