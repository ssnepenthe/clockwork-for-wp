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
		$this->assertEquals( 'SELECT * FROM posts', $request->databaseQueries[0]['query'] );
		$this->assertEquals( 500, $request->databaseQueries[0]['duration'] );
		$this->assertEquals( 'POST', $request->databaseQueries[0]['model'] );

		$this->assertEquals(
			'SELECT whatever FROM wherever',
			$request->databaseQueries[1]['query']
		);
		$this->assertEquals( 250, $request->databaseQueries[1]['duration'] );
		$this->assertEquals( '(unknown)', $request->databaseQueries[1]['model'] );

		$this->assertEquals( 'SELECT * FROM users LIMIT 5', $request->databaseQueries[2]['query'] );
		$this->assertEquals( 750, $request->databaseQueries[2]['duration'] );
		$this->assertEquals( 'USER', $request->databaseQueries[2]['model'] );
	}
}
