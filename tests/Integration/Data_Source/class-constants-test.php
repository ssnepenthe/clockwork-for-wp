<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Constants;
use PHPUnit\Framework\TestCase;

class Constants_Test extends TestCase {
	const STRING_VALUE = 'apples';
	const TRUE_VALUE = true;
	const FALSE_VALUE = false;
	const NULL_VALUE = null;
	const INT_VALUE = 5;
	const FLOAT_VALUE = 4.7;
	const ARRAY_VALUE = [ 'a' => 'b' ];

	/** @test */
	public function it_correctly_records_constant_data() {
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

			// Undefined constants are labelled as such.
			[ 'constant' => "{$class}::NOT_DEFINED" ],
		] );

		$request = new Request();
		$data_source->resolve( $request );
		$data = $request->userData( 'WordPress' )->toArray()[0];

		$this->assertEquals( [
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
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Constants',
			],
		], $data );
	}
}
