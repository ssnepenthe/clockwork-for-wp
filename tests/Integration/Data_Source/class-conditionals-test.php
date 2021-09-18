<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Conditionals;
use PHPUnit\Framework\TestCase;

class Conditionals_Test extends TestCase {
	public function test_resolve_can_ignore_conditional_based_on_when() {
		$namespace = __NAMESPACE__;

		$data_source = new Conditionals( [
			[ 'conditional' => "{$namespace}\\boolean_truthy", 'when' => function() { return false; } ],
			[ 'conditional' => "{$namespace}\\boolean_falsey", 'when' => function() { return true; } ],
		] );
		$request = $data_source->resolve( new Request() );
		$data = $request->userData( 'WordPress' )->toArray()[0];
		unset( $data['__meta'] );

		$this->assertCount( 1, $data );
	}

	public function test_resolve_allows_for_label_override() {
		$namespace = __NAMESPACE__;

		$data_source = new Conditionals( [
			[ 'conditional' => "{$namespace}\\boolean_truthy", 'label' => 'test_label()' ],
		] );
		$request = $data_source->resolve( new Request() );

		$this->assertSame(
			'test_label()',
			$request->userData( 'WordPress' )->toArray()[0][0]['conditional']
		);
	}

	public function test_resolve_non_boolean_descriptions() {
		$namespace = __NAMESPACE__;

		$data_source = new Conditionals( [
			[ 'conditional' => "{$namespace}\\string_truthy" ],
			[ 'conditional' => "{$namespace}\\string_falsey" ],
			[ 'conditional' => "{$namespace}\\integer_truthy" ],
			[ 'conditional' => "{$namespace}\\integer_falsey" ],
		] );
		$request = $data_source->resolve( new Request() );
		$data = $request->userData( 'WordPress' )->toArray()[0];

		$this->assertSame( 'TRUTHY (1)', $data[0]['value'] );
		$this->assertSame( 'TRUTHY ("a")', $data[1]['value'] );
		$this->assertSame( 'FALSEY (0)', $data[2]['value'] );
		$this->assertSame( 'FALSEY ("")', $data[3]['value'] );
	}

	public function test_resolve_boolean_descriptions() {
		$namespace = __NAMESPACE__;

		$data_source = new Conditionals( [
			[ 'conditional' => "{$namespace}\\boolean_truthy" ],
			[ 'conditional' => "{$namespace}\\boolean_falsey" ],
		] );
		$request = $data_source->resolve( new Request() );
		$data = $request->userData( 'WordPress' )->toArray()[0];

		$this->assertSame( 'TRUE', $data[0]['value'] );
		$this->assertSame( 'FALSE', $data[1]['value'] );
	}

	public function test_resolve_sort_order() {
		$namespace = __NAMESPACE__;

		$data_source = new Conditionals( [
			[ 'conditional' => "{$namespace}\\boolean_truthy" ],
			[ 'conditional' => "{$namespace}\\boolean_falsey" ],
			[ 'conditional' => "{$namespace}\\string_truthy" ],
			[ 'conditional' => "{$namespace}\\string_falsey" ],
			[ 'conditional' => "{$namespace}\\integer_truthy" ],
			[ 'conditional' => "{$namespace}\\integer_falsey" ],
		] );
		$request = $data_source->resolve( new Request() );
		$data = $request->userData( 'WordPress' )->toArray()[0];

		$this->assertSame( "{$namespace}\\integer_truthy()", $data[0]['conditional'] );
		$this->assertSame( "{$namespace}\\string_truthy()", $data[1]['conditional'] );
		$this->assertSame( "{$namespace}\\boolean_truthy()", $data[2]['conditional'] );
		$this->assertSame( "{$namespace}\\integer_falsey()", $data[3]['conditional'] );
		$this->assertSame( "{$namespace}\\string_falsey()", $data[4]['conditional'] );
		$this->assertSame( "{$namespace}\\boolean_falsey()", $data[5]['conditional'] );
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
function integer_truthy() {
	return 1;
}
function integer_falsey() {
	return 0;
}
