<?php

namespace Clockwork_For_Wp\Tests\Browser\Api;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

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
	public function it_correctly_serves_previous_requests() {
		// @todo There seems to be a flaw in the Clockwork FileStorage::previous() logic...
		// 		 We are getting one fewer response than expected.
		// 		 For now we compensate by making this third request.
		$id1 = $this->get( '/' )
			->header( 'x-clockwork-id' );
		$id2 = $this->get( '/' )
			->header( 'x-clockwork-id' );
		$id3 = $this->get( '/' )
			->header( 'x-clockwork-id' );

		$this->get( "/__clockwork/{$id3}/previous" )
			->assert_ok()
			->assert_json( function( $decoded ) use ( $id1 ) {
				$request = end( $decoded );

				$this->assertIsArray( $request );
				$this->assertArrayHasKey( 'id', $request );
				$this->assertEquals( $id1, $request['id'] );
			} );
	}

	/** @test */
	public function it_correctly_serves_next_requests() {
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
	}

	/** @test */
	public function it_correctly_serves_latest_requests() {
		$id = $this->get( '/' )
			->header( 'x-clockwork-id' );

		// @todo Update route to end with latest - currently /__clockwork/latest123 also works.
		$this->get( '/__clockwork/latest' )
			->assert_ok()
			->assert_json_path( 'id', $id );
	}

	/** @test */
	public function it_correctly_extends_requests() {
		// @todo Test that response is actually extended - will require xdebug profiler.
		$this->markTestIncomplete( 'Not yet implemented' );
	}
}
