<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wp_Object_Cache;
use PHPUnit\Framework\TestCase;

class Wp_Object_Cache_Test extends TestCase {
	/** @test */
	public function it_correctly_records_object_cache_data() {
		$data_source = new Wp_Object_Cache();
		$request = new Request();

		$data_source->hit();
		$data_source->hit( 2 );

		$data_source->miss();
		$data_source->miss( 3 );

		$data_source->write();
		$data_source->write( 4 );

		$data_source->delete();
		$data_source->delete( 5 );

		$data_source->resolve( $request );

		$this->assertEquals( [
			'Reads' => 7,
			'Hits' => 3,
			'Misses' => 4,
			'Writes' => 5,
			'Deletes' => 6,
			'__meta' => [
				'showAs' => 'counters',
			],
		], $request->userData( 'Caching' )->toArray()[0] );
	}

	/** @test */
	public function it_filters_out_unused_stats() {
		$data_source = new Wp_Object_Cache();
		$request = new Request();

		$data_source->hit();
		$data_source->hit( 2 );

		$data_source->delete();
		$data_source->delete( 3 );

		$data_source->resolve( $request );

		$this->assertEquals( [
			'Reads' => 3,
			'Hits' => 3,
			'Deletes' => 4,
			'__meta' => [
				'showAs' => 'counters',
			],
		], $request->userData( 'Caching' )->toArray()[0] );
	}

	/** @test */
	public function it_doesnt_create_the_userdata_entry_when_there_are_no_cache_stats() {
		$data_source = new Wp_Object_Cache();
		$request = new Request();

		$data_source->resolve( $request );

		$this->assertEquals( [], $request->userData );
	}
}
