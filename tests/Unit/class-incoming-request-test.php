<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Tests\Unit;

use Clockwork_For_Wp\Incoming_Request;
use PHPUnit\Framework\TestCase;

class Incoming_Request_Test extends TestCase {
	/**
	 * @test
	 */
	public function test_get_header(): void {
		$request = new Incoming_Request( [
			'headers' => [
				'apple' => 'banana',
				'content-type' => 'text/html',
				'one-two-three-four' => 'iseethreedashes',
			],
		] );

		// Casing is normalized.
		$this->assertSame( 'banana', $request->header( 'apple' ) );
		$this->assertSame( 'banana', $request->header( 'Apple' ) );
		$this->assertSame( 'banana', $request->header( 'APPLE' ) );

		// HTTP_ prefix is not acknowledged.
		$this->assertNull( $request->header( 'HTTP_CONTENT_TYPE' ) );
		$this->assertSame( 'text/html', $request->header( 'content-type' ) );

		// Underscores are converted to dashes.
		$this->assertSame( 'iseethreedashes', $request->header( 'ONE_TWO_THREE_FOUR' ) );

		// Default can be specified for missing headers.
		$this->assertSame( 'defaultvalue', $request->header( 'NOT_SET', 'defaultvalue' ) );
	}

	/**
	 * @test
	 */
	public function test_extract_headers(): void {
		$server = [
			// Discarded.
			'SOME_VARIABLE' => 'some-value',

			// HTTP_ prefix stripped.
			'HTTP_APPLE' => 'banana',
			'HTTP_ZEBRA' => 'yak',

			// Untouched.
			'CONTENT_TYPE' => 'text/html',
			'CONTENT_LENGTH' => '0',
			'CONTENT_MD5' => 'abcdef',

			// Underscores to dashes.
			'HTTP_ONE_TWO_THREE_FOUR' => 'iseethreedashes',
		];

		$this->assertSame( [
			'apple' => 'banana',
			'zebra' => 'yak',
			'content-type' => 'text/html',
			'content-length' => '0',
			'content-md5' => 'abcdef',
			'one-two-three-four' => 'iseethreedashes',
		], Incoming_Request::extract_headers( $server ) );
	}
}
