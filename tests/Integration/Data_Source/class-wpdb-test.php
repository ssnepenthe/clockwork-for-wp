<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wpdb;
use PHPUnit\Framework\TestCase;

class Wpdb_Test extends TestCase {
	/** @test */
	public function it_correctly_records_wpdb_data() {
		$data_source = new Wpdb( $detect_dupes = false, $slow_only = false, $slow_threshold = 50 );
		$request = new Request();
		$time = microtime( true );

		$data_source->set_queries( [
			[ 'select * from posts', 500, $time ],
			[ 'select whatever from wherever', 250, $time ],
		] );
		$data_source->add_query( 'select * from users limit 5', 750, $time );

		// Duplicate query detection is disabled.
		$data_source->add_query( 'select * from posts', 500, $time );

		$data_source->resolve( $request );

		// @todo More thorough testing of capitalization and model guessing functionality.
		$this->assertEquals( 'SELECT * FROM posts', $request->databaseQueries[0]['query'] );
		$this->assertEquals( 500, $request->databaseQueries[0]['duration'] );
		$this->assertEquals( $time, $request->databaseQueries[0]['time'] );
		$this->assertEquals( 'POST', $request->databaseQueries[0]['model'] );

		$this->assertEquals(
			'SELECT whatever FROM wherever',
			$request->databaseQueries[1]['query']
		);
		$this->assertEquals( 250, $request->databaseQueries[1]['duration'] );
		$this->assertEquals( $time, $request->databaseQueries[1]['time'] );
		$this->assertEquals( '(unknown)', $request->databaseQueries[1]['model'] );

		$this->assertEquals( 'SELECT * FROM users LIMIT 5', $request->databaseQueries[2]['query'] );
		$this->assertEquals( 750, $request->databaseQueries[2]['duration'] );
		$this->assertEquals( $time, $request->databaseQueries[2]['time'] );
		$this->assertEquals( 'USER', $request->databaseQueries[2]['model'] );

		// Ensure duplicate queries are not logged.
		$this->assertEmpty( $request->log()->toArray() );
	}

	/** @test */
	public function it_can_detect_duplicate_queries() {
		$data_source = new Wpdb( $detect_dupes = true, $slow_only = false, $slow_threshold = 50 );
		$request = new Request();
		$untested_duration = 50;
		$untested_time = microtime( true );

		$data_source->set_queries( [
			[
				'
					select
						*
					from
						posts
				',
				$untested_duration,
				$untested_time
			],
			[ 'select * from users', $untested_duration, $untested_time ],
			[ 'select * from posts;', $untested_duration, $untested_time ],
			[ 'select * from tags', $untested_duration, $untested_time ],
			[ '   select    *    from     posts   ', $untested_duration, $untested_time ],
			[ 'select * from users', $untested_duration, $untested_time ],
		] );

		$data_source->resolve( $request );

		$log = $request->log()->toArray();

		$this->assertCount( 2, $log );

		$this->assertEquals(
			'Duplicate query: "select * from posts" run 3 times',
			$log[0]['message']
		);
		$this->assertEquals(
			'Duplicate query: "select * from users" run 2 times',
			$log[1]['message']
		);
	}

	/** @test */
	public function it_can_limit_query_logging_to_slow_queries() {
		$untested_time = microtime( true );
		$queries = [
			[ 'select * from posts', 50, $untested_time ],
			[ 'select * from users', 100, $untested_time ],
			[ 'select * from tags', 150, $untested_time ],
		];

		$data_source = new Wpdb( $detect_dupes = false, $slow_only = true, $slow_threshold = 75 );
		$request = new Request();

		$data_source->set_queries( $queries );
		$data_source->resolve( $request );

		$this->assertCount( 2, $request->databaseQueries );
		$this->assertEquals( 'SELECT * FROM users', $request->databaseQueries[0]['query'] );
		$this->assertEquals( 'SELECT * FROM tags', $request->databaseQueries[1]['query'] );

		$data_source = new Wpdb( $detect_dupes = false, $slow_only = true, $slow_threshold = 100 );
		$request = new Request();

		$data_source->set_queries( $queries );
		$data_source->resolve( $request );

		$this->assertCount( 1, $request->databaseQueries );
		$this->assertEquals( 'SELECT * FROM tags', $request->databaseQueries[0]['query'] );
	}
}
