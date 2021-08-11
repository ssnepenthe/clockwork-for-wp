<?php

namespace Clockwork_For_Wp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use function Clockwork_For_Wp\array_get;
use function Clockwork_For_Wp\array_has;
use function Clockwork_For_Wp\array_set;
use function Clockwork_For_Wp\describe_callable;
use function Clockwork_For_Wp\describe_unavailable_callable;
use function Clockwork_For_Wp\describe_value;
use function Clockwork_For_Wp\prepare_rest_route;
use function Clockwork_For_Wp\prepare_wpdb_query;

class Helpers_Test extends TestCase {
	/** @test */
	public function test_array_get() {
		// Key exists at top level.
		$array = [ 'a' => 'b', 'c' => [ 'd' => 'e' ] ];
		$this->assertEquals( 'b', array_get( $array, 'a' ) );

		// Key has dot but exists at top level.
		$array = [ 'a' => 'b', 'c.d' => 'e' ];
		$this->assertEquals( 'e', array_get( $array, 'c.d' ) );

		// Key not at top level and no dot returns default.
		$array = [ 'a' => 'b', 'c' => [ 'd' => 'e' ] ];
		$this->assertNull( array_get( $array, 'b' ) );

		// Arbitrary depth.
		$array = [ 'a' => [ 'b' => [ 'c' => [ 'd' => [ 'e' => 'f' ] ] ] ] ];
		$this->assertEquals(
			[ 'b' => [ 'c' => [ 'd' => [ 'e' => 'f' ] ] ] ],
			array_get( $array, 'a' )
		);
		$this->assertEquals( [ 'c' => [ 'd' => [ 'e' => 'f' ] ] ], array_get( $array, 'a.b' ) );
		$this->assertEquals( [ 'd' => [ 'e' => 'f' ] ], array_get( $array, 'a.b.c' ) );
		$this->assertEquals( [ 'e' => 'f' ], array_get( $array, 'a.b.c.d' ) );
		$this->assertEquals( 'f', array_get( $array, 'a.b.c.d.e' ) );
		$this->assertNull( array_get( $array, 'e.d.c.b.a' ) );

		// Arbitrary default value.
		$array = [ 'a' => 'b' ];
		$this->assertEquals( 'defaultvalue', array_get( $array, 'c.d', 'defaultvalue' ) );

		// Null values.
		$array = [ 'a' => null, 'b' => [ 'c' => null ] ];
		$this->assertNull( array_get( $array, 'a', 'defaultvalue' ) );
		$this->assertNull( array_get( $array, 'b.c', 'defaultvalue' ) );

		// Numeric keys.
		$array = [ 'a' => [
			[ 'b' => 'c' ],
			[ 'd' => 'e' ],
		] ];
		$this->assertEquals( 'c', array_get( $array, 'a.0.b' ) );
		$this->assertEquals( 'e', array_get( $array, 'a.1.d' ) );
	}

	/** @test */
	public function test_array_has() {
		$array = [ 'a' => 'b', 'c' => [ 'd' => 'e', 'f' => [ 'g' => 'h' ] ] ];

		$this->assertTrue( array_has( $array, 'a' ) );
		$this->assertTrue( array_has( $array, 'c' ) );
		$this->assertTrue( array_has( $array, 'c.d' ) );
		$this->assertTrue( array_has( $array, 'c.f' ) );
		$this->assertTrue( array_has( $array, 'c.f.g' ) );

		$this->assertFalse( array_has( $array, 'b' ) );
		$this->assertFalse( array_has( $array, 'd.c' ) );
	}

	/** @test */
	public function test_array_set() {
		$array = [ 'a' => [ 'b' => [ 'c' => 'd' ] ] ];
		array_set( $array, 'a.b.c', 'e' );
		$this->assertEquals( [ 'a' => [ 'b' => [ 'c' => 'e' ] ] ], $array );

		$array = [ 'a' => 'b' ];
		array_set( $array, 'a.b.c', 'd' );
		$this->assertEquals( [ 'a' => [ 'b' => [ 'c' => 'd' ] ] ], $array );

		$array = [ 'a' => [ 'b' => [ 'c' => 'd' ] ] ];
		array_set( $array, 'e', 'f' );
		$this->assertEquals( [ 'a' => [ 'b' => [ 'c' => 'd' ] ], 'e' => 'f' ], $array );

		$array = [ 'a' => [ 'b' => [ 'c' => 'd' ] ] ];
		array_set( $array, 'e.f', 'g' );
		$this->assertEquals( [ 'a' => [ 'b' => [ 'c' => 'd' ] ], 'e' => [ 'f' => 'g' ] ], $array );

		$array = [];
		array_set( $array, 'a.b.c', 'd' );
		$this->assertEquals( [ 'a' => [ 'b' => [ 'c' => 'd' ] ] ], $array );
	}

	/** @test */
	public function test_describe_unavailable_callable() {
		// Strings.
		$this->assertSame( 'pfx_some_func()', describe_unavailable_callable( 'pfx_some_func' ) );
		$this->assertSame( 'Pfx::some_func()', describe_unavailable_callable( 'Pfx::some_func' ) );

		// Arrays.
		$this->assertSame(
			'Pfx::some_func()',
			describe_unavailable_callable( [ 'Pfx', 'some_func' ] )
		);

		// Unknowns.
		$this->assertSame( '(Unknown)', describe_unavailable_callable( ' ' ) );
		$this->assertSame( '(Unknown)', describe_unavailable_callable( 3 ) );
		$this->assertSame( '(Unknown)', describe_unavailable_callable( [] ) );
		$this->assertSame( '(Unknown)', describe_unavailable_callable( [ '', ' ' ] ) );
		$this->assertSame( '(Unknown)', describe_unavailable_callable( [ 3, 4 ] ) );
	}

	/** @test */
	public function test_describe_callable() {
		$namespace = __NAMESPACE__;

		// Non-callable.
		$this->assertEquals( '(Non-callable value)', describe_callable( 'apples' ) );

		// String.
		$this->assertEquals( 'array_map()', describe_callable( 'array_map' ) );

		// Array.
		// Instance method.
		$this->assertEquals(
			"{$namespace}\\Describe_Callable_Tester->instance_method()",
			describe_callable( [ new Describe_Callable_Tester, 'instance_method' ] )
		);

		// Static method.
		$this->assertEquals(
			"{$namespace}\\Describe_Callable_Tester::static_method()",
			describe_callable( [ Describe_Callable_Tester::class, 'static_method' ] )
		);
		$this->assertEquals(
			"{$namespace}\\Describe_Callable_Tester::static_method()",
			describe_callable( __NAMESPACE__ . '\\Describe_Callable_Tester::static_method' )
		);

		// Closure.
		$file = __FILE__;
		$line = __LINE__ + 3;
		$this->assertEquals(
			"Closure ({$file}, line {$line})",
			describe_callable( function() {} )
		);

		// Invokable.
		$this->assertEquals(
			"{$namespace}\\Describe_Callable_Tester->__invoke()",
			describe_callable( new Describe_Callable_Tester() )
		);

		// @todo Keep an eye out for values we might use to test default/fallback return value.
	}

	/** @test */
	public function test_describe_value() {
		$this->assertEquals( 'NULL', describe_value( null ) );
		$this->assertEquals( 'TRUE', describe_value( true ) );
		$this->assertEquals( 'FALSE', describe_value( false ) );
		$this->assertEquals( '"stringval"', describe_value( 'stringval' ) );
		$this->assertEquals( '42', describe_value( 42 ) );
		$this->assertEquals( '4.2', describe_value( 4.2 ) );
		// @todo !!!
		$this->assertEquals( '(NON-SCALAR VALUE)', describe_value( [] ) );
		$this->assertEquals( '(NON-SCALAR VALUE)', describe_value( new \stdClass() ) );
	}

	/** @test */
	public function test_prepare_rest_route() {
		$handlers_array = [
			'methods' => [ 'GET' => true, 'POST' => true ],
			'callback' => [ 'a', 'b' ],
			'permission_callback' => [ 'c', 'd' ],
		];

		$this->assertEquals(
			[ [ 'GET', 'POST' ], [ 'a', 'b' ], [ 'c', 'd' ] ],
			prepare_rest_route( $handlers_array )
		);

		$handlers_array = [
			'methods' => [ 'GET' => true, 'POST' => true ],
		];

		$this->assertEquals(
			[ [ 'GET', 'POST' ], null, null ],
			prepare_rest_route( $handlers_array )
		);
	}

	/** @test */
	public function test_prepare_wpdb_query() {
		$time = microtime( true );
		$query_array = [ 'select * from wherever', 0.2, 'irrelevant-callstack', $time ];

		$this->assertEquals(
			[ 'select * from wherever', 200, $time ],
			prepare_wpdb_query( $query_array )
		);
	}
}

class Describe_Callable_Tester {
	public function __invoke() {}

	public function instance_method() {}

	public static function static_method() {}
}
