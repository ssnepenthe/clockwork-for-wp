<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;
use Clockwork_For_Wp\Tests\Metadata;

class Disabled_Test extends Test_Case {
	protected function test_config(): array {
		return [
			'enable' => false,
		];
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

		// Current metadata count should be 0.
		$this->assertSame( 0, static::api()->metadata_count() );
	}
}
