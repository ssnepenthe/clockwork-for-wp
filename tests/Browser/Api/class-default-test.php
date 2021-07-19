<?php

namespace Clockwork_For_Wp\Tests\Browser\Api;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

use function Clockwork_For_Wp\Tests\clean_metadata_files;

// @todo Test clockwork search functionality.
class Default_Test extends Test_Case {
	/** @test */
	public function it_does_not_require_authentication_by_default() {
		$id = $this->get( '/' )
			->header( 'x-clockwork-id' );

		$response = $this->get( "/__clockwork/{$id}" )
			->assert_ok();

		// Yes, I know this is redundant...
		$this->assertNotEquals( 403, $response->status() );
	}

	/** @test */
	public function it_returns_a_true_auth_token_value_by_default() {
		$this->post( '/__clockwork/auth' )
			->assert_ok()
			->assert_json_path( 'token', true );
	}

	/** @test */
	public function it_correctly_serves_id_and_latest_requests() {
		$id1 = $this->get( '/' )
			->header( 'x-clockwork-id' );
		$id2 = $this->get( '/' )
			->header( 'x-clockwork-id' );

		$this->get( "/__clockwork/{$id1}" )
			->assert_ok()
			->assert_json_path( '0.id', $id1 );

		// @todo Update route to end with latest - currently /__clockwork/latest123 also works.
		$this->get( '/__clockwork/latest' )
			->assert_ok()
			->assert_json_path( '0.id', $id2 );
	}

	/** @test */
	public function it_correctly_serves_next_and_previous_requests() {
		$id1 = $this->get( '/' )
			->header( 'x-clockwork-id' );
		$id2 = $this->get( '/' )
			->header( 'x-clockwork-id' );

		$this->get( "/__clockwork/{$id1}/next" )
			->assert_ok()
			->assert_json( function( $decoded ) use ( $id2 ) {
				$request = reset( $decoded );

				$this->assertIsArray( $request );
				$this->assertArrayHasKey( 'id', $request );
				$this->assertEquals( $id2, $request['id'] );
			} );

		$this->get( "/__clockwork/{$id2}/previous" )
			->assert_ok()
			->assert_json( function( $decoded ) use ( $id1 ) {
				$request = end( $decoded );

				$this->assertIsArray( $request );
				$this->assertArrayHasKey( 'id', $request );
				$this->assertEquals( $id1, $request['id'] );
			} );
	}

	/** @test */
	public function it_correctly_handles_count_argument() {
		clean_metadata_files();

		$id1 = $this->get( '/' )
			->header( 'x-clockwork-id' );
		$id2 = $this->get( '/' )
			->header( 'x-clockwork-id' );
		$id3 = $this->get( '/' )
			->header( 'x-clockwork-id' );
		$id4 = $this->get( '/' )
			->header( 'x-clockwork-id' );
		$id5 = $this->get( '/' )
			->header( 'x-clockwork-id' );

		// @todo Should we test that the default returns up to 10 results?
		$this->get( "/__clockwork/{$id3}/next" )
			->assert_ok()
			->assert_json( function( $decoded ) {
				$this->assertCount( 2, $decoded );
			} );

		$this->get( "/__clockwork/{$id3}/next/1" )
			->assert_ok()
			->assert_json( function( $decoded ) {
				$this->assertCount( 1, $decoded );
			} );

		$this->get( "/__clockwork/{$id3}/previous" )
			->assert_ok()
			->assert_json( function( $decoded ) {
				$this->assertCount( 2, $decoded );
			} );

		$this->get( "/__clockwork/{$id3}/previous/1" )
			->assert_ok()
			->assert_json( function( $decoded ) {
				$this->assertCount( 1, $decoded );
			} );
	}

	/** @test */
	public function it_correctly_handles_except_filter() {
		$id = $this->get( '/' )
			->header( 'x-clockwork-id' );
		$response_size = count( $this->get( "/__clockwork/{$id}" )->decode_response_json() );

		// ID search returns single request.
		$this->get( "/__clockwork/{$id}?except=id" )
			->assert_json( function( $decoded ) use ( $response_size ) {
				$this->assertArrayNotHasKey( 'id', $decoded );
				$this->assertCount( $response_size - 1, $decoded );
			} );

		$this->get( "/__clockwork/{$id}?except=id,version" )
			->assert_json( function( $decoded ) use ( $response_size ) {
				$this->assertArrayNotHasKey( 'id', $decoded );
				$this->assertArrayNotHasKey( 'version', $decoded );
				$this->assertCount( $response_size - 2, $decoded );
			} );

		$this->get( "/__clockwork/{$id}?except=id,version,type" )
			->assert_json( function( $decoded ) use ( $response_size ) {
				$this->assertArrayNotHasKey( 'id', $decoded );
				$this->assertArrayNotHasKey( 'version', $decoded );
				$this->assertArrayNotHasKey( 'type', $decoded );
				$this->assertCount( $response_size - 3, $decoded );
			} );

		// Latest search returns a list of requests.
		$this->get( '/__clockwork/latest?except=id' )
			->assert_json( function( $decoded ) use ( $response_size ) {
				$this->assertCount( 1, $decoded );
				$this->assertArrayNotHasKey( 'id', $decoded[0] );
				$this->assertCount( $response_size - 1, $decoded[0] );
			} );
	}

	/** @test */
	public function it_correctly_handles_only_filter() {
		$id = $this->get( '/' )
			->header( 'x-clockwork-id' );

		// ID search returns a single request.
		$this->get( "/__clockwork/{$id}?only=id" )
			->assert_json( function( $decoded ) {
				$this->assertArrayHasKey( 'id', $decoded );
				$this->assertCount( 1, $decoded );
			} );

		$this->get( "/__clockwork/{$id}?only=id,version" )
			->assert_json( function( $decoded ) {
				$this->assertArrayHasKey( 'id', $decoded );
				$this->assertArrayHasKey( 'version', $decoded );
				$this->assertCount( 2, $decoded );
			} );

		$this->get( "/__clockwork/{$id}?only=id,version,type" )
			->assert_json( function( $decoded ) {
				$this->assertArrayHasKey( 'id', $decoded );
				$this->assertArrayHasKey( 'version', $decoded );
				$this->assertArrayHasKey( 'type', $decoded );
				$this->assertCount( 3, $decoded );
			} );

		// Latest search returns a list of requests.
		$this->get( '/__clockwork/latest?only=id,version' )
			->assert_json( function( $decoded ) {
				$this->assertCount( 1, $decoded );
				$this->assertArrayHasKey( 'id', $decoded[0] );
				$this->assertArrayHasKey( 'version', $decoded[0] );
				$this->assertCount( 2, $decoded[0] );
			} );


		// Only takes precedence over except.
		$this->get( "/__clockwork/{$id}?only=id&except=id" )
			->assert_json( function( $decoded ) {
				$this->assertArrayHasKey( 'id', $decoded );
				$this->assertCount( 1, $decoded );
			} );
	}

	/** @test */
	public function it_correctly_extends_requests() {
		// @todo Test that response is actually extended - will require xdebug profiler.
		$this->markTestIncomplete( 'Not yet implemented' );
	}
}
