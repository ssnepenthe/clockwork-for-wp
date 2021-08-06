<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wp;
use PHPUnit\Framework\TestCase;

class Wp_Test extends TestCase {
	/** @test */
	public function it_correctly_records_wp_data() {
		$data_source = new Wp();
		$request = new Request();

		$data_source->add_variable( 'key1', 'value1' );
		$data_source->add_variable( 'key2', 'value2' );

		$data_source->resolve( $request );

		$this->assertEquals( [
			[
				'Variable' => 'key1',
				'Value' => 'value1',
			],
			[
				'Variable' => 'key2',
				'Value' => 'value2',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Request',
			],
		], $request->userData( 'WordPress' )->toArray()[0] );
	}
}
