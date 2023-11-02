<?php

namespace Clockwork_For_Wp\Tests\Unit;

use PHPUnit\Framework\TestCase;

use function Clockwork_For_Wp\array_only;
use function Clockwork_For_Wp\describe_callable;
use function Clockwork_For_Wp\describe_unavailable_callable;
use function Clockwork_For_Wp\describe_value;
use function Clockwork_For_Wp\prepare_rest_route;
use function Clockwork_For_Wp\prepare_wpdb_query;
use stdClass;

class Helpers_Test extends TestCase {
	/** @test */
	public function test_array_only(): void {
		$array = [ 'a' => 'b', 'c' => 'd', 'e' => 'f' ];

		$this->assertSame( [ 'a' => 'b', 'c' => 'd' ], array_only( $array, [ 'a', 'c' ] ) );
		$this->assertEmpty( array_only( $array, [ 'g' ] ) );
	}

	/** @test */
	public function test_describe_unavailable_callable(): void {
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
	public function test_describe_callable(): void {
		$namespace = __NAMESPACE__;

		// Non-callable.
		$this->assertEquals( '(Non-callable value)', describe_callable( 'apples' ) );

		// String.
		$this->assertEquals( 'array_map()', describe_callable( 'array_map' ) );

		// Array.
		// Instance method.
		$this->assertEquals(
			"{$namespace}\\Describe_Callable_Tester->instance_method()",
			describe_callable( [ new Describe_Callable_Tester(), 'instance_method' ] )
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
			describe_callable( static fn() => null )
		);

		// Invokable.
		$this->assertEquals(
			"{$namespace}\\Describe_Callable_Tester->__invoke()",
			describe_callable( new Describe_Callable_Tester() )
		);

		// @todo Keep an eye out for values we might use to test default/fallback return value.
	}

	/** @test */
	public function test_describe_value(): void {
		$this->assertEquals( 'NULL', describe_value( null ) );
		$this->assertEquals( 'TRUE', describe_value( true ) );
		$this->assertEquals( 'FALSE', describe_value( false ) );
		$this->assertEquals( '"stringval"', describe_value( 'stringval' ) );
		$this->assertEquals( '42', describe_value( 42 ) );
		$this->assertEquals( '4.2', describe_value( 4.2 ) );
		// @todo !!!
		$this->assertEquals( '(NON-SCALAR VALUE)', describe_value( [] ) );
		$this->assertEquals( '(NON-SCALAR VALUE)', describe_value( new stdClass() ) );
	}

	/** @test */
	public function test_prepare_rest_route(): void {
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
	public function test_prepare_wpdb_query(): void {
		$time = \microtime( true );
		$query_array = [ 'select * from wherever', 0.2, 'irrelevant-callstack', $time ];

		$this->assertEquals(
			[ 'select * from wherever', 200, $time ],
			prepare_wpdb_query( $query_array )
		);
	}
}

class Describe_Callable_Tester {
	public function __invoke(): void {
	}

	public function instance_method(): void {
	}

	public static function static_method(): void {
	}
}
