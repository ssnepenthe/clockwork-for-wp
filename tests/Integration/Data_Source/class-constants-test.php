<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Constants;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class Constants_Test extends TestCase {
	const STRING_VALUE = 'apples';
	const TRUE_VALUE = true;
	const FALSE_VALUE = false;
	const NULL_VALUE = null;
	const INT_VALUE = 5;
	const FLOAT_VALUE = 4.7;
	const ARRAY_VALUE = [ 'a' => 'b' ];

	public function test_resolve_can_ignore_constant_based_on_when() {
		$class = __CLASS__;
		$data_source = new Constants( [
			[ 'constant' => "{$class}::STRING_VALUE", 'when' => function() { return true; } ],
			[ 'constant' => "{$class}::TRUE_VALUE", 'when' => function() { return false; } ],
		] );

		$data = $data_source->resolve( new Request() )->userData( 'WordPress' )->toArray()[0];
		unset( $data['__meta'] );

		$this->assertSame( [
			[
				'constant' => "{$class}::STRING_VALUE",
				'value' => '"apples"',
			]
		], $data );
	}

	public function test_resolve_defined_constants() {
		$class = __CLASS__;
		$data_source = new Constants( [
			// Strings are wrapped in quotes.
			[ 'constant' => "{$class}::STRING_VALUE" ],

			// Booleans and null are displayed as string equivalent, all uppercase.
			[ 'constant' => "{$class}::TRUE_VALUE" ],
			[ 'constant' => "{$class}::FALSE_VALUE" ],
			[ 'constant' => "{$class}::NULL_VALUE" ],

			// Numeric values are displayed as string equivalent.
			[ 'constant' => "{$class}::INT_VALUE" ],
			[ 'constant' => "{$class}::FLOAT_VALUE" ],

			// Non-scalar values are labelled as such (pending improvements to describe_value()).
			[ 'constant' => "{$class}::ARRAY_VALUE" ],
		] );

		$data = $data_source->resolve( new Request() )->userData( 'WordPress' )->toArray()[0];
		unset( $data['__meta'] );

		$this->assertSame( [
			[
				'constant' => "{$class}::ARRAY_VALUE",
				'value' => '(NON-SCALAR VALUE)',
			],
			[
				'constant' => "{$class}::FALSE_VALUE",
				'value' => 'FALSE',
			],
			[
				'constant' => "{$class}::FLOAT_VALUE",
				'value' => '4.7',
			],
			[
				'constant' => "{$class}::INT_VALUE",
				'value' => '5',
			],
			[
				'constant' => "{$class}::NULL_VALUE",
				'value' => 'NULL',
			],
			[
				'constant' => "{$class}::STRING_VALUE",
				'value' => '"apples"',
			],
			[
				'constant' => "{$class}::TRUE_VALUE",
				'value' => 'TRUE',
			]
		], $data );
	}

	public function test_resolve_undefined_constants() {
		$class = __CLASS__;
		$data_source = new Constants( [
			// Undefined constants are labelled as such.
			[ 'constant' => "{$class}::NOT_DEFINED" ],
		] );

		$data = $data_source->resolve( new Request() )->userData( 'WordPress' )->toArray()[0][0];

		$this->assertSame( [
			'constant' => "{$class}::NOT_DEFINED",
			'value' => '(NOT DEFINED)',
		], $data );
	}

	public function test_resolve_sort_order() {
		$class = __CLASS__;
		$data_source = new Constants( [
			[ 'constant' => "{$class}::STRING_VALUE" ],
			[ 'constant' => "{$class}::TRUE_VALUE" ],
			[ 'constant' => "{$class}::FALSE_VALUE" ],
			[ 'constant' => "{$class}::NULL_VALUE" ],
			[ 'constant' => "{$class}::INT_VALUE" ],
			[ 'constant' => "{$class}::FLOAT_VALUE" ],
			[ 'constant' => "{$class}::ARRAY_VALUE" ],
			[ 'constant' => "{$class}::NOT_DEFINED" ],
		] );

		$data = $data_source->resolve( new Request() )->userData( 'WordPress' )->toArray()[0];
		unset( $data['__meta'] );

		$this->assertSame( [
			// Results are sorted alphabetically by constant.
			[
				'constant' => "{$class}::ARRAY_VALUE",
				'value' => '(NON-SCALAR VALUE)',
			],
			[
				'constant' => "{$class}::FALSE_VALUE",
				'value' => 'FALSE',
			],
			[
				'constant' => "{$class}::FLOAT_VALUE",
				'value' => '4.7',
			],
			[
				'constant' => "{$class}::INT_VALUE",
				'value' => '5',
			],
			[
				'constant' => "{$class}::NOT_DEFINED",
				'value' => '(NOT DEFINED)',
			],
			[
				'constant' => "{$class}::NULL_VALUE",
				'value' => 'NULL',
			],
			[
				'constant' => "{$class}::STRING_VALUE",
				'value' => '"apples"',
			],
			[
				'constant' => "{$class}::TRUE_VALUE",
				'value' => 'TRUE',
			]
		], $data );
	}

	public function test_resolve_empty_constant_list() {
		$class = __CLASS__;
		$data_source = new Constants( [
			[ 'constant' => "{$class}::STRING_VALUE", 'when' => function() { return false; } ],
		] );

		$this->assertEmpty( $data_source->resolve( new Request() )->userData );
	}

	public function test_from_no_constants() {
		$data_source = Constants::from( [] );

		$this->assertEmpty( $data_source->resolve( new Request() )->userData );
	}

	public function test_from_non_array_constants() {
		$this->expectException( InvalidArgumentException::class );

		Constants::from( [ 'constants' => 4 ] );
	}

	public function test_from_string_constant() {
		$class = __CLASS__;
		$data_source = Constants::from( [ 'constants' => [ "{$class}::STRING_VALUE" ] ] );

		$this->assertSame( [
			'constant' => "{$class}::STRING_VALUE",
			'value' => '"apples"',
		], $data_source->resolve( new Request() )->userData( 'WordPress' )->toArray()[0][0] );
	}

	public function test_from_non_array_or_string_constant() {
		$this->expectException( InvalidArgumentException::class );

		Constants::from( [ 'constants' => [ 4 ] ] );
	}

	public function test_from_array_constant() {
		$class = __CLASS__;
		$data_source = Constants::from( [
			'constants' => [ [ 'constant' => "{$class}::STRING_VALUE" ] ],
		] );

		$this->assertSame( [
			'constant' => "{$class}::STRING_VALUE",
			'value' => '"apples"',
		], $data_source->resolve( new Request() )->userData( 'WordPress' )->toArray()[0][0] );
	}

	public function test_from_mixed_constants() {
		$class = __CLASS__;
		$data_source = Constants::from( [
			'constants' => [
				[ 'constant' => "{$class}::STRING_VALUE" ],
				"{$class}::TRUE_VALUE",
			],
		] );

		$data = $data_source->resolve( new Request() )->userData( 'WordPress' )->toArray()[0];
		unset( $data['__meta'] );

		$this->assertSame( [
			[
				'constant' => "{$class}::STRING_VALUE",
				'value' => '"apples"',
			],
			[
				'constant' => "{$class}::TRUE_VALUE",
				'value' => 'TRUE',
			],
		], $data );
	}

	public function test_from_constant_missing_constant_key() {
		$this->expectException( InvalidArgumentException::class );

		Constants::from( [ 'constants' => [ [] ] ] );
	}

	public function test_from_constant_non_string_constant_value() {
		$this->expectException( InvalidArgumentException::class );

		Constants::from( [ 'constants' => [ [ 'constant' => 4 ] ] ] );
	}

	public function test_from_constant_non_callable_when() {
		$this->expectException( InvalidArgumentException::class );

		$class = __CLASS__;
		Constants::from( [
			'constants' => [ [ 'constant' => "{$class}::STRING_VALUE", 'when' => 4 ] ],
		] );
	}

	public function test_from_constant_with_when() {
		$class = __CLASS__;
		$data_source = Constants::from( [
			'constants' => [
				[
					'constant' => "{$class}::STRING_VALUE",
					'when' => function() { return true; },
				],
			],
		] );

		$this->assertSame( [
			'constant' => "{$class}::STRING_VALUE",
			'value' => '"apples"',
		], $data_source->resolve( new Request() )->userData( 'WordPress' )->toArray()[0][0] );
	}

	public function test_from_constant_without_when() {
		$class = __CLASS__;
		$data_source = Constants::from( [
			'constants' => [ [ 'constant' => "{$class}::STRING_VALUE" ] ],
		] );

		$this->assertSame( [
			'constant' => "{$class}::STRING_VALUE",
			'value' => '"apples"',
		], $data_source->resolve( new Request() )->userData( 'WordPress' )->toArray()[0][0] );
	}
}
