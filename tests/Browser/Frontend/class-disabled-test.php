<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Disabled_Test extends Test_Case {
	protected static function required_plugins() : array {
		return [ 'cfw-disabled' ];
	}

	/** @test */
	public function it_does_not_send_clockwork_headers() {
		$this->get( '/' )
			->assert_header_missing( 'x-clockwork-id' )
			->assert_header_missing( 'x-clockwork-version' );
	}

	/** @test */
	public function it_does_not_store_request_data() {
		// @todo Not sure how best to test this... List entire contents of storage dir before/after?
		$this->markTestIncomplete( 'Not yet implemented' );
	}
}
