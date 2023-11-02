<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Rest_Api;
use PHPUnit\Framework\TestCase;

class Rest_Api_Test extends TestCase {
	/** @test */
	public function it_correctly_records_rest_api_data(): void {
		$data_source = new Rest_Api();
		$request = new Request();

		$data_source->add_route( 'a/b', 'GET' );
		$data_source->add_route( 'c/d', 'GET, POST', 'array_map' );
		$data_source->add_route( 'e/f', [ 'POST', 'GET' ], 'array_filter', 'array_walk' );

		$data_source->resolve( $request );

		$this->assertEquals( [
			[
				'Path' => 'a/b',
				'Methods' => 'GET',
				'Callback' => null,
				'Permission Callback' => null,
			],
			[
				'Path' => 'c/d',
				'Methods' => 'GET, POST',
				'Callback' => 'array_map()',
				'Permission Callback' => null,
			],
			[
				'Path' => 'e/f',
				'Methods' => 'POST, GET',
				'Callback' => 'array_filter()',
				'Permission Callback' => 'array_walk()',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'REST Routes',
			],
		], $request->userData( 'Routing' )->toArray()[0] );
	}

	/** @test */
	public function it_doesnt_create_the_userdata_entry_when_there_are_no_routes(): void {
		$data_source = new Rest_Api();
		$request = new Request();

		$data_source->resolve( $request );

		$this->assertEquals( [], $request->userData );
	}
}
