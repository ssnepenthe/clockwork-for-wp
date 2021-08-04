<?php

namespace Clockwork_For_Wp\Tests\Browser;

use Goutte\Client;
use PHPUnit\Framework\TestCase;
use function Clockwork_For_Wp\Tests\clean_metadata_files;

// @todo Restore db to default state before each test.
// @todo Clear cfw-data dir before each test.
// @todo Configurable base uri.
class Test_Case extends TestCase {
	const PASSWORD = 'nothing-to-see-here-folks';

	protected static $ajax_url;
	protected static $content_url;
	protected static $test_plugin_active = false;

	public static function setUpBeforeClass(): void {
		static::$test_plugin_active = '' !== static::ajax_url();

		clean_metadata_files();
	}

	public function setUp(): void {
		if ( ! static::$test_plugin_active ) {
			$this->markTestSkipped( 'The test plugin does not appear to be active' );
		}
	}

	public function tearDown(): void {
		// @todo Move to test helper plugin.
		clean_metadata_files();
	}

	protected static function base_uri() : string {
		return 'http://one.wordpress.test';
	}

	protected static function ajax_url() : string {
		if ( is_string( static::$ajax_url ) ) {
			return static::$ajax_url;
		}

		static::$ajax_url = trim(
			( new Client )->request( 'GET', static::base_uri() )
				->filter( '#cfw-coh-ajaxurl' )
				->text( '' )
		);

		return static::$ajax_url;
	}

	protected static function content_url() : string {
		if ( is_string( static::$content_url ) ) {
			return static::$content_url;
		}

		$client = new Client;
		$ajax_url = static::ajax_url();

		$client->request( 'GET', "{$ajax_url}?action=cfw_coh_content_url" );
		$response = json_decode( $client->getResponse()->getContent(), true );

		static::$content_url = trim( $response['data'] );

		return static::$content_url;
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

		if ( ! empty( $config = $this->test_config() ) ) {
			$uri .= '?' . http_build_query( $config );
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
