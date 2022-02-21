<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork_For_Wp\Clockwork_Provider;
use Clockwork_For_Wp\Config;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Storage_Factory;
use Null_Storage_For_Tests;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
*/
class Clockwork_Provider_Test extends TestCase {
	/** @test */
	public function the_clock_function_can_be_disabled() {
		$this->assertFalse( function_exists( 'clock' ) );

		$plugin = new Plugin( [], [
			Config::class => new Config( [
				'register_helpers' => false,
				'storage' => [
					'driver' => 'null',
				],
			] ),
		] );
		$plugin->register( new Clockwork_Provider( $plugin ) );
		$plugin[ Storage_Factory::class ]->register_custom_factory( 'null', function() {
			return new Null_Storage_For_Tests();
		} );

		$plugin->lock();

		$this->assertFalse( \function_exists( 'clock' ) );
	}

	/** @test */
	public function the_clock_function_can_be_enabled() {
		$this->assertFalse( function_exists( 'clock' ) );

		$plugin = new Plugin( [], [
			Config::class => new Config( [
				'register_helpers' => true,
				'storage' => [
					'driver' => 'null',
				],
			] ),
		] );
		$plugin->register( new Clockwork_Provider( $plugin ) );
		$plugin[ Storage_Factory::class ]->register_custom_factory( 'null', function() {
			return new Null_Storage_For_Tests();
		} );
		$plugin->lock();

		$this->assertTrue( \function_exists( 'clock' ) );
	}
}
