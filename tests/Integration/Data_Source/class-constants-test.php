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

		$data_source = new Constants(
			"{$class}::STRING_VALUE",
			"{$class}::TRUE_VALUE",
			"{$class}::FALSE_VALUE",
			"{$class}::NULL_VALUE",
			"{$class}::INT_VALUE",
			"{$class}::FLOAT_VALUE",
			"{$class}::ARRAY_VALUE",
			"{$class}::NOT_DEFINED"
		);

		$request = new Request();
		$data_source->resolve( $request );
		$data = $request->userData( 'WordPress' )->toArray()[0];

		$this->assertEquals( [
			// Strings are wrapped in quotes.
			[
				'Name' => "{$class}::STRING_VALUE",
				'Value' => '"apples"',
			],

			// Booleans are displayed as string equivalent, all uppercase.
			[
				'Name' => "{$class}::TRUE_VALUE",
				'Value' => 'TRUE',
			],
			[
				'Name' => "{$class}::FALSE_VALUE",
				'Value' => 'FALSE',
			],

			// Null is displayed as string equivalent, all uppercase.
			[
				'Name' => "{$class}::NULL_VALUE",
				'Value' => 'NULL',
			],

			// Numeric values are displayed as string equivalent.
			[
				'Name' => "{$class}::INT_VALUE",
				'Value' => '5',
			],
			[
				'Name' => "{$class}::FLOAT_VALUE",
				'Value' => '4.7',
			],

			// Non-scalar values are labelled as such.
			// @todo
			[
				'Name' => "{$class}::ARRAY_VALUE",
				'Value' => '(NON-SCALAR VALUE)',
			],

			// Undefined constants are labelled as such.
			[
				'Name' => "{$class}::NOT_DEFINED",
				'Value' => '(NOT DEFINED)',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Constants',
			],
		], $data );
	}
}
