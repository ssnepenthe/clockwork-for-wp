<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wp_Query;
use PHPUnit\Framework\TestCase;

class Wp_Query_Test extends TestCase {
	/** @test */
	public function it_correctly_records_wp_query_data(): void {
		$data_source = new Wp_Query();
		$request = new Request();

		$data_source->set_query_vars( [
			'z' => 'a',
			'y' => 'b',
		] );

		$data_source->add_query_var( 'x', 'c' );

		$data_source->resolve( $request );

		// Should be sorted by variable name.
		$this->assertEquals( [
			[
				'Variable' => 'x',
				'Value' => 'c',
			],
			[
				'Variable' => 'y',
				'Value' => 'b',
			],
			[
				'Variable' => 'z',
				'Value' => 'a',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Query Vars',
			],
		], $request->userData( 'WordPress' )->toArray()[0] );
	}
}
