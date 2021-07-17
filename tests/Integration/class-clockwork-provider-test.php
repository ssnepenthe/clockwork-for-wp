<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork_For_Wp\Clockwork_Provider;
use Clockwork_For_Wp\Config;
use Clockwork_For_Wp\Plugin;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
*/
class Clockwork_Provider_Test extends TestCase {
	/** @test */
	public function the_clock_function_can_be_disabled() {
		$this->assertFalse( function_exists( 'clock' ) );

		$plugin = new Plugin( [ Clockwork_Provider::class ], [
			Config::class => new Config( [
				'register_helpers' => false,
			] ),
		] );

		$this->assertFalse( \function_exists( 'clock' ) );
	}

	/** @test */
	public function the_clock_function_can_be_enabled() {
		$this->assertFalse( function_exists( 'clock' ) );

		$plugin = new Plugin( [ Clockwork_Provider::class ], [
			Config::class => new Config( [
				'register_helpers' => true,
			] ),
		] );

		$this->assertTrue( \function_exists( 'clock' ) );
	}
}
