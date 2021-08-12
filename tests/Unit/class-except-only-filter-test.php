<?php

namespace Clockwork_For_Wp\Tests\Unit;

use Clockwork_For_Wp\Data_Source\Except_Only_Filter;
use PHPUnit\Framework\TestCase;

class Except_Only_Filter_Test extends TestCase {
	/** @test */
	public function test_default() {
		$filter = new Except_Only_Filter( [], [] );

		$this->assertTrue( $filter( 'testvalue' ) );
	}

	/** @test */
	public function test_except() {
		$filter = new Except_Only_Filter( [ '^test', 'test$' ], [] );

		$this->assertFalse( $filter( 'testvalue' ) );
		$this->assertFalse( $filter( 'valuetest' ) );
		$this->assertTrue( $filter( 'othervalue' ) );
	}

	/** @test */
	public function test_only() {
		$filter = new Except_Only_Filter( [], [ '^test', 'test$' ] );

		$this->assertTrue( $filter( 'testvalue' ) );
		$this->assertTrue( $filter( 'valuetest' ) );
		$this->assertFalse( $filter( 'othervalue' ) );
	}

	/** @test */
	public function test_except_and_only() {
		// "Only" takes precedence over "except".
		$filter = new Except_Only_Filter( [ '^test', 'test$' ], [ '^test', 'test$' ] );

		$this->assertTrue( $filter( 'testvalue' ) );
		$this->assertTrue( $filter( 'valuetest' ) );
		$this->assertFalse( $filter( 'othervalue' ) );
	}
}
