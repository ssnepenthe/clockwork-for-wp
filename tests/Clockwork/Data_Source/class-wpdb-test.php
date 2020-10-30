<?php

namespace Clockwork_For_Wp\Tests\Clockwork\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wpdb;
use PHPUnit\Framework\TestCase;

class Wpdb_Test extends TestCase {
	/** @test */
	public function it_correctly_records_wpdb_data() {
		$data_source = new Wpdb();
		$request = new Request();

		$data_source->set_queries( [
			[ 'select * from posts', 500, 'POST' ],
			[ 'select whatever from wherever', 250, 'UNKNOWN' ],
		] );
		$data_source->add_query( 'select * from users limit 5', 750, 'USER' );

		$data_source->resolve( $request );

		// @todo More thorough testing of capitalization and model guessing functionality.
		$this->assertEquals( [
			[
				'query' => 'SELECT * FROM posts',
				'bindings' => [],
				'duration' => 500,
				'connection' => null,
				'file' => null,
				'line' => null,
				'trace' => null,
				'model' => 'POST',
			],
			[
				'query' => 'SELECT whatever FROM wherever',
				'bindings' => [],
				'duration' => 250,
				'connection' => null,
				'file' => null,
				'line' => null,
				'trace' => null,
				'model' => '(unknown)',
			],
			[
				'query' => 'SELECT * FROM users LIMIT 5',
				'bindings' => [],
				'duration' => 750,
				'connection' => null,
				'file' => null,
				'line' => null,
				'trace' => null,
				'model' => 'USER',
			],
		], $request->databaseQueries );
	}
}
