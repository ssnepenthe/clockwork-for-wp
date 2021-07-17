<?php

namespace Clockwork_For_Wp\Tests\Clockwork\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Core;
use PHPUnit\Framework\TestCase;

class Core_Test extends TestCase {
	/** @test */
	public function it_correctly_records_core_data() {
		$data_source = new Core( '4.7', microtime( true ) );

		$request = new Request( [
			'time' => $_SERVER['REQUEST_TIME_FLOAT']
		] );

		$data_source->resolve( $request );

		$this->assertEquals( [
			'WP Version' => '4.7',
			'__meta' => [
				'showAs' => 'counters',
			],
		], $request->userData( 'WordPress' )->toArray()[0] );

		$this->assertEquals( 'Total Execution', $request->timelineData[0]['description'] );
		$this->assertEquals( 'Core Timer Start', $request->timelineData[1]['description'] );
		$this->assertSame( 0.0, $request->timelineData[1]['duration'] );
	}
}
