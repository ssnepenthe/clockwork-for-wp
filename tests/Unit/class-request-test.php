<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Tests\Unit;

use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Request;
use PHPUnit\Framework\TestCase;
use SimpleWpRouting\Support\RequestContext;

class Request_Test extends TestCase {
	public function test_get_header(): void {
		$content_type = 'text/html';
		$auth = 'somekey';
		$default_value = 'somedefault';

		$request = $this->create_request( [
			'headers' => [
				'content-type' => $content_type,
				'x_clockwork_auth' => $auth,
			],
		] );

		$this->assertSame( $content_type, $request->get_header( 'CONTENT-TYPE' ) );
		$this->assertSame( $auth, $request->get_header( 'X-CLOCKWORK-AUTH', $default_value ) );
		$this->assertNull( $request->get_header( 'NOT-SET' ) );
		$this->assertSame( $default_value, $request->get_header( 'NOT-SET', $default_value ) );
	}

	public function test_get_input(): void {
		$except = 'some,except';
		$only = 'other,only';
		$default_value = 'somedefault';

		$request = $this->create_request( [
			'input' => [
				'except' => $except,
				'only' => $only,
			],
		] );

		$this->assertSame( $except, $request->get_input( 'except' ) );
		$this->assertSame( $only, $request->get_input( 'only', $default_value ) );
		$this->assertNull( $request->get_input( 'not-set' ) );
		$this->assertSame( $default_value, $request->get_input( 'not-set', $default_value ) );
	}

	public function test_is_heartbeat(): void {
		// Must be POST request.
		$request = $this->create_request( [
			'method' => 'GET',
			'uri' => '/wp-admin/admin-ajax.php',
			'input' => [
				'action' => 'heartbeat',
			],
		] );

		$this->assertFalse( $request->is_heartbeat() );

		$request = $this->create_request( [
			'method' => 'POST',
			'uri' => '/wp-admin/admin-ajax.php',
			'input' => [
				'action' => 'heartbeat',
			],
		] );

		$this->assertTrue( $request->is_heartbeat() );

		// URI must end in 'admin-ajax.php'.
		$request = $this->create_request( [
			'method' => 'POST',
			'uri' => '/wp-json/wp/v2',
			'input' => [
				'action' => 'heartbeat',
			],
		] );

		$this->assertFalse( $request->is_heartbeat() );

		$request = $this->create_request( [
			'method' => 'POST',
			'uri' => '/wp-admin/admin-ajax.php',
			'input' => [
				'action' => 'heartbeat',
			],
		] );

		$this->assertTrue( $request->is_heartbeat() );

		$request = $this->create_request( [
			'method' => 'POST',
			'uri' => '/custom-admin/admin-ajax.php',
			'input' => [
				'action' => 'heartbeat',
			],
		] );

		$this->assertTrue( $request->is_heartbeat() );

		// 'action' input must be set to 'heartbeat'.
		$request = $this->create_request( [
			'method' => 'POST',
			'uri' => '/wp-admin/admin-ajax.php',
			'input' => [
				'action' => 'someaction',
			],
		] );

		$this->assertFalse( $request->is_heartbeat() );

		$request = $this->create_request( [
			'method' => 'POST',
			'uri' => '/wp-admin/admin-ajax.php',
			'input' => [
				'action' => 'heartbeat',
			],
		] );

		$this->assertTrue( $request->is_heartbeat() );
	}

	public function test_is_json(): void {
		$request = $this->create_request( [
			'headers' => [
				'CONTENT-TYPE' => 'text/html',
			],
		] );

		$this->assertFalse( $request->is_json() );

		$request = $this->create_request( [
			'headers' => [
				//
			],
		] );

		$this->assertFalse( $request->is_json() );

		$request = $this->create_request( [
			'headers' => [
				'CONTENT-TYPE' => 'application/json',
			],
		] );

		$this->assertTrue( $request->is_json() );
	}

	private function create_request( array $request = [] ): Request {
		$ir = new IncomingRequest( [
			'method' => $request['method'] ?? 'GET',
			'uri' => $request['uri'] ?? '/',
			'input' => $request['input'] ?? [],
			'cookies' => $request['cookies'] ?? [],
		] );
		$rc = new RequestContext( $request['method'] ?? 'GET', $request['headers'] ?? [] );

		return new Request( $ir, $rc );
	}
}
