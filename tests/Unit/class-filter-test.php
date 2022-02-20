<?php

namespace Clockwork_For_Wp\Tests\Unit;

use Clockwork_For_Wp\Data_Source\Filter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class Filter_Test extends TestCase {
	/** @test */
	public function test_default() {
		$filter = new Filter();

		$this->assertTrue( $filter( 'testvalue' ) );
	}

	/** @test */
	public function test_except() {
		$filter = ( new Filter() )
			->except( [ '^test', 'test$' ] );

		$this->assertFalse( $filter( 'testvalue' ) );
		$this->assertFalse( $filter( 'valuetest' ) );
		$this->assertTrue( $filter( 'othervalue' ) );
	}

	/** @test */
	public function test_only() {
		$filter = ( new Filter() )
			->only( [ '^test', 'test$' ] );

		$this->assertTrue( $filter( 'testvalue' ) );
		$this->assertTrue( $filter( 'valuetest' ) );
		$this->assertFalse( $filter( 'othervalue' ) );
	}

	/** @test */
	public function test_except_and_only() {
		// "Only" takes precedence over "except".
		$filter = ( new Filter() )
			->except( [ '^test', 'test$' ] )
			->only( [ '^test', 'test$' ] );

		$this->assertTrue( $filter( 'testvalue' ) );
		$this->assertTrue( $filter( 'valuetest' ) );
		$this->assertFalse( $filter( 'othervalue' ) );
	}

	/** @test */
	public function test_to_closure() {
		$filter = ( new Filter() )
			->except( [ '^test' ] )
			->to_closure();

		$this->assertFalse( $filter( 'testvalue' ) );
		$this->assertTrue( $filter( 'valuetest' ) );
		$this->assertTrue( $filter( 'othervalue' ) );
	}

	/** @test */
	public function test_to_closure_with_key() {
		$filter = ( new Filter() )
			->except( [ '^test' ] )
			->to_closure( 'somekey' );

		$this->assertFalse( $filter( [ 'somekey' => 'testvalue' ] ) );
		$this->assertTrue( $filter( [ 'somekey' => 'valuetest' ] ) );
		$this->assertTrue( $filter( [ 'somekey' => 'othervalue' ] ) );
	}

	/** @test */
	public function test_to_closure_with_key_non_array_value() {
		$this->expectException( InvalidArgumentException::class );

		$filter = ( new Filter() )
			->to_closure( 'somekey' );

		$filter( 'somevalue' );
	}

	/** @test */
	public function test_to_closure_with_key_when_key_doesnt_exist() {
		$this->expectException( InvalidArgumentException::class );

		$filter = ( new Filter() )
			->to_closure( 'somekey' );

		$filter( [ 'wrongkey' => 'somevalue' ] );
	}
}
