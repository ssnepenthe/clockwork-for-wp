<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork_For_Wp\Clockwork_Provider;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Read_Only_Configuration;
use Clockwork_For_Wp\Storage_Factory;
use Clockwork_For_Wp\Tests\Creates_Config;
use Null_Storage_For_Tests;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
*/
class Clockwork_Provider_Test extends TestCase {
	use Creates_Config;

	/** @test */
	public function the_clock_function_can_be_disabled() {
		$this->assertFalse( function_exists( 'clock' ) );

		$plugin = $this->create_plugin( [
			'register_helpers' => false,
			'storage' => [
				'driver' => 'null',
			],
		] );

		$plugin->lock();

		$this->assertFalse( \function_exists( 'clock' ) );
	}

	/** @test */
	public function the_clock_function_can_be_enabled() {
		$this->assertFalse( function_exists( 'clock' ) );

		$plugin = $this->create_plugin( [
			'register_helpers' => true,
			'storage' => [
				'driver' => 'null',
			],
		] );

		$plugin->lock();

		$this->assertTrue( \function_exists( 'clock' ) );
	}

	private function create_plugin( array $user_config = [] ) {
		$plugin = new Plugin( [], [
			Read_Only_Configuration::class => $this->create_config( $user_config ),
		] );

		$plugin->register( new Clockwork_Provider( $plugin ) );

		$plugin->get_pimple()->extend( Storage_Factory::class, function( $factory ) {
			$factory->register_custom_factory( 'null', function() {
				return new Null_Storage_For_Tests();
			} );

			return $factory;
		} );

		return $plugin;
	}
}
