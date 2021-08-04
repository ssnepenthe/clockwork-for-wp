<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;
use Clockwork_For_Wp\Tests\Metadata;

class Disabled_Test extends Test_Case {
	public function setUp(): void {
		parent::setUp();

		$this->with_config( [
			'enable' => false,
		] );
	}

	/** @test */
	public function it_does_not_send_clockwork_headers() {
		$this->get( '/' )
			->assert_header_missing( 'x-clockwork-id' )
			->assert_header_missing( 'x-clockwork-version' );
	}

	/** @test */
	public function it_does_not_store_request_data() {
		$this->get( '/' );

		$this->assertCount( 0, Metadata::all() );
	}
}


