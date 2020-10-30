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

		$data = $request->userData( 'WordPress' )->toArray()[0];
		$timeline = $request->timelineData;

		$this->assertEquals( [
			'WP Version' => '4.7',
			'__meta' => [
				'showAs' => 'counters',
			],
		], $data );

		$this->assertArrayHasKey( 'total', $timeline );
		$this->assertEquals( 'Total Execution', $timeline['total']['description'] );

		$this->assertArrayHasKey( 'core_timer', $timeline );
		$this->assertEquals( 'Core Timer Start', $timeline['core_timer']['description'] );
	}
}
