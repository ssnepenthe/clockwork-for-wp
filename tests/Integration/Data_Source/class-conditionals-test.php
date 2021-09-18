<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Conditionals;
use PHPUnit\Framework\TestCase;

class Conditionals_Test extends TestCase {
	/** @test */
	public function it_correctly_records_conditionals_data() {
		// Function is correctly described.
		// Value is either "TRUE", "TRUTHY (*)", "FALSE" or "FALSEY (*)".
		// Rows are sorted by value, then function.
		// Panel has title "Conditionals".
		$namespace = __NAMESPACE__;

		$data_source = new Conditionals( [
			[ 'conditional' => "{$namespace}\\boolean_truthy" ],
			[ 'conditional' => "{$namespace}\\boolean_falsey" ],
			[ 'conditional' => "{$namespace}\\string_truthy" ],
			[ 'conditional' => "{$namespace}\\string_falsey" ]
		] );

		$request = new Request();
		$data_source->resolve( $request );
		$data = $request->userData( 'WordPress' )->toArray()[0];

		$this->assertEquals( [
			[
				'conditional' => "{$namespace}\string_truthy()",
				'value' => 'TRUTHY ("a")',
			],
			[
				'conditional' => "{$namespace}\boolean_truthy()",
				'value' => 'TRUE',
			],
			[
				'conditional' => "{$namespace}\string_falsey()",
				'value' => 'FALSEY ("")',
			],
			[
				'conditional' => "{$namespace}\boolean_falsey()",
				'value' => 'FALSE',
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
function boolean_falsey() {
	return false;
}
function string_truthy() {
	return 'a';
}
function string_falsey() {
	return '';
}
