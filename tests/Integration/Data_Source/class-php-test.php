<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Php;
use PHPUnit\Framework\TestCase;

class Php_Test extends TestCase {
	protected $backed_up_superglobals = [];

	private function back_up_superglobals() {
		$this->backed_up_superglobals = [
			'cookie' => $_COOKIE,
			'get' => $_GET,
			'post' => $_POST,
			'server' => $_SERVER,
		];
	}

	private function restore_superglobals() {
		$_COOKIE = $this->backed_up_superglobals['cookie'];
		$_GET = $this->backed_up_superglobals['get'];
		$_POST = $this->backed_up_superglobals['post'];
		$_SERVER = $this->backed_up_superglobals['server'];
	}

	/** @test */
	public function it_correctly_records_php_data() {
		// @todo What is best approach to testing $_SESSION superglobal?
		$this->back_up_superglobals();

		$_COOKIE = [
			'badcookiekey' => 'badcookievalue',
			'sensitivecookiekey' => 'sensitivecookievalue',
			'goodcookiekey' => 'goodcookievalue',
		];
		$_GET = [
			'badgetkey' => 'badgetvalue',
			'sensitivegetkey' => 'sensitivegetvalue',
			'goodgetkey' => 'goodgetvalue',
		];
		$_POST = [
			'badpostkey' => 'badpostvalue',
			'sensitivepostkey' => 'sensitivepostvalue',
			'goodpostkey' => 'goodpostvalue',
		];
		$_SERVER['HTTP_COOKIE'] = 'badcookiekey=badcookievalue;'
			. ' sensitivecookiekey=sensitivecookievalue;'
			. ' goodcookiekey=goodcookievalue';

		$data_source = new Php( '/^bad/i', '/^sensitive/i' );
		$request = new Request();

		$data_source->resolve( $request );

		$this->restore_superglobals();

		$this->assertEquals( [
			'badcookiekey' => '*removed*',
			'sensitivecookiekey' => '*removed*',
			'goodcookiekey' => 'goodcookievalue',
		], $request->cookies );

		$this->assertEquals( [
			'badgetkey' => '*removed*',
			'sensitivegetkey' => '*removed*',
			'goodgetkey' => 'goodgetvalue',
		], $request->getData );

		$this->assertEquals( [
			'badpostkey' => '*removed*',
			'sensitivepostkey' => '*removed*',
			'goodpostkey' => 'goodpostvalue',
		], $request->postData );

		$this->assertEquals(
			[
				'Cookie' => [
					'badcookiekey=*removed*; sensitivecookiekey=*removed*; goodcookiekey=goodcookievalue',
				],
			],
			$request->headers
		);
	}
}
