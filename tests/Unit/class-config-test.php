<?php

namespace Clockwork_For_Wp\Tests\Unit;

use Clockwork_For_Wp\Config;
use PHPUnit\Framework\TestCase;

class Config_Test extends TestCase {
	/** @test */
	public function test_get() {
		$config = new Config( [ 'a' => 'b', 'c' => [ 'd' => 'e' ] ] );

		$this->assertEquals( 'b', $config->get( 'a' ) );
		$this->assertEquals( [ 'd' => 'e' ], $config->get( 'c' ) );
		$this->assertEquals( 'e', $config->get( 'c.d' ) );
		$this->assertNull( $config->get( 'f' ) );
		$this->assertEquals( 'defaultvalue', $config->get( 'f', 'defaultvalue' ) );
	}

	/** @test */
	public function test_has() {
		$config = new Config( [ 'a' => 'b', 'c' => [ 'd' => 'e' ] ] );

		$this->assertTrue( $config->has( 'a' ) );
		$this->assertTrue( $config->has( 'c' ) );
		$this->assertTrue( $config->has( 'c.d' ) );

		$this->assertFalse( $config->has( 'f' ) );
		$this->assertFalse( $config->has( 'c.f' ) );
	}

	/** @test */
	public function test_set() {
		$config = new Config( [] );

		$config->set( 'a.b.c', 'd' );

		$this->assertTrue( $config->has( 'a.b.c' ) );
		$this->assertEquals( 'd', $config->get( 'a.b.c' ) );
	}
}
