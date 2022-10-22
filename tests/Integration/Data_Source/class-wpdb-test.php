<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork_For_Wp\Data_Source\Wpdb;
use Clockwork_For_Wp\Plugin;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Data_Source_Factory;
use PHPUnit\Framework\TestCase;
use ToyWpEventManagement\EventDispatcherInterface;
use ToyWpEventManagement\EventManagerInterface;

class Wpdb_Test extends TestCase {
	protected function pattern_model_map() {
		// @todo Can we pull this from the bundled config? Would require a method for removing WP constants as dependencies of the config.php file.
		return [
			// @todo Should we include "old tables"?
			'/blog(?:_version)?s$/' => 'BLOG',
			'/comment(?:s|meta)$/' => 'COMMENT',
			'/links$/' => 'LINK',
			'/options$/' => 'OPTION',
			'/post(?:s|meta)$/' => 'POST',
			'/registration_log$/' => 'REGISTRATION',
			'/signups$/' => 'SIGNUP',
			'/site(?:categories|meta)?$/' => 'SITE',
			'/term(?:s|_relationships|_taxonomy|meta)$/' => 'TERM',
			'/user(?:s|meta)$/' => 'USER',
		];
	}

	/** @test */
	public function it_correctly_records_wpdb_data() {
		$pattern_model_map = $this->pattern_model_map();
		$pattern_model_map['/somewhere/'] = 'TESTMODEL';

		$data_source = new Wpdb( $detect_dupes = false, $pattern_model_map );
		$request = new Request();
		$time = microtime( true );

		$data_source->set_queries( [
			[ 'select * from posts', 500, $time ],
			[ 'select whatever from wherever', 250, $time ],
		] );
		$data_source->add_query( 'select * from users limit 5', 750, $time );

		// Duplicate query detection is disabled.
		$data_source->add_query( 'select * from posts', 500, $time );

		// Custom model patterns work.
		$data_source->add_query( 'select something from somewhere', 250, $time );

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

		// Duplicate queries are recorded...
		$this->assertEquals( 'SELECT * FROM posts', $request->databaseQueries[3]['query'] );

		// But not logged...
		$this->assertEmpty( $request->log()->toArray() );

		// Custom model patterns are used.
		$this->assertEquals( 'TESTMODEL', $request->databaseQueries[4]['model'] );
	}

	/** @test */
	public function it_can_detect_duplicate_queries() {
		$data_source = new Wpdb( $detect_dupes = true, [] );
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

		$data_source = $this->create_data_source_via_factory( [
			'slow_only' => true,
			'slow_threshold' => 75,
		] );
		$request = new Request();

		$data_source->set_queries( $queries );
		$data_source->resolve( $request );

		$this->assertCount( 2, $request->databaseQueries );
		$this->assertEquals( 'SELECT * FROM users', $request->databaseQueries[0]['query'] );
		$this->assertEquals( 'SELECT * FROM tags', $request->databaseQueries[1]['query'] );

		$data_source = $this->create_data_source_via_factory( [
			'slow_only' => true,
			'slow_threshold' => 100,
		] );
		$request = new Request();

		$data_source->set_queries( $queries );
		$data_source->resolve( $request );

		$this->assertCount( 1, $request->databaseQueries );
		$this->assertEquals( 'SELECT * FROM tags', $request->databaseQueries[0]['query'] );
	}

	/** @test */
	public function it_can_identify_models_with_custom_identifier_callbacks() {
		$data_source = new Wpdb( $detect_dupes = false, $this->pattern_model_map() );

		// It should use the first callback to return a string value.
		$data_source->add_custom_model_identifier( function( $query ) {
			return null;
		} );
		$data_source->add_custom_model_identifier( function( $query ) {
			return 'TESTMODEL';
		} );
		$data_source->add_custom_model_identifier( function( $query ) {
			return 'TESTMODEL2';
		} );

		$data_source->add_query( 'select * from posts', 500, microtime( true ) );

		$request = new Request();

		$data_source->resolve( $request );

		$this->assertEquals( 'SELECT * FROM posts', $request->databaseQueries[0]['query'] );
		$this->assertEquals( 'TESTMODEL', $request->databaseQueries[0]['model'] );
	}

	protected function create_data_source_via_factory( $config = [] ) {
		$em = $this->createMock( EventManagerInterface::class );
		$ed = $this->createMock( EventDispatcherInterface::class );

		$plugin = new class( $em, $ed ) extends Plugin {
			public function __construct( $eventManager, $eventDispatcher ) {
				parent::__construct();

				$this->eventManager = $eventManager;
				$this->eventDispatcher = $eventDispatcher;
			}
		};

		return ( new Data_Source_Factory( $plugin ) )->create( 'wpdb', [
			'pattern_model_map' => $config['pattern_model_map'] ?? $this->pattern_model_map(),
			'slow_only' => $config['slow_only'] ?? false,
			'slow_threshold' => $config['slow_threshold'] ?? 50
		] );
	}
}
