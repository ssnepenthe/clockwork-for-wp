<?php

namespace Clockwork_For_Wp\Tests\Clockwork\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Conditionals;
use PHPUnit\Framework\TestCase;

class Conditionals_Test extends TestCase {
	/** @test */
	public function it_correctly_records_conditionals_data() {
		// Function is correctly described.
		// Value is either "TRUE" or "FALSE".
		// Rows are sorted by value, then function.
		// Panel has title "Conditionals".
		$namespace = __NAMESPACE__;

		$data_source = new Conditionals(
			"{$namespace}\\boolean_truthy",
			"{$namespace}\\boolean_falsy",
			"{$namespace}\\string_truthy",
			"{$namespace}\\string_falsy"
		);

		$request = new Request();
		$data_source->resolve( $request );
		$data = $request->userData( 'WordPress' )->toArray()[0];

		$this->assertEquals( [
			[
				'Function' => "{$namespace}\boolean_truthy()",
				'Value' => 'TRUE',
			],
			[
				'Function' => "{$namespace}\string_truthy()",
				'Value' => 'TRUE',
			],
			[
				'Function' => "{$namespace}\boolean_falsy()",
				'Value' => 'FALSE',
			],
			[
				'Function' => "{$namespace}\string_falsy()",
				'Value' => 'FALSE',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Conditionals',
			],
		], $data );
	}
}

function boolean_truthy() {
	return true;
}
function boolean_falsy() {
	return false;
}
function string_truthy() {
	return 'a';
}
function string_falsy() {
	return '';
}
