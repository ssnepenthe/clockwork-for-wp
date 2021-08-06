<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Default_Test extends Test_Case {
	/** @test */
	public function it_adds_clockwork_id_header() {
		$this->get( '/' )
			// @todo ->assert_header_matches( 'x-clockwork-id', '/\d{10,}-\d{4}-\d+$/' );
			->assert_header( 'x-clockwork-id', function( $value ) {
				$this->assertRegExp( '/\d{10,}-\d{4}-\d+$/', $value );
			} );
	}

	/** @test */
	public function it_adds_clockwork_version_header() {
		$this->get( '/' )
			->assert_header( 'x-clockwork-version', \Clockwork\Clockwork::VERSION );
	}

	/** @test */
	public function it_stores_request_data() {
		$id = $this->get( '/' )
			->header( 'x-clockwork-id' );

		$metadata = static::api()->metadata_by_id( $id );

		$this->assertSame( $id, $metadata['id'] );
		$this->assertSame( '/', $metadata['uri'] );
	}

	/** @test */
	public function it_is_disabled_by_default_for_internal_clockwork_routes() {
		$this->get( '/__clockwork/app' )
			->assert_header_missing( 'x-clockwork-id' )
			->assert_header_missing( 'x-clockwork-version' );

		// @todo Consider comparing contents of cfw-data dir before and after test?
	}

	/** @test */
	public function it_is_disabled_by_default_for_options_requests() {
		$this->request( 'OPTIONS', '/' )
			->assert_header_missing( 'x-clockwork-id' )
			->assert_header_missing( 'x-clockwork-version' );
	}
}
