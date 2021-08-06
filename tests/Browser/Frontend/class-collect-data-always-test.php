<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Collect_Data_Always_Test extends Test_Case {
	protected function test_config(): array {
		return [
			'enable' => false,
			'collect_data_always' => true,
		];
	}

	/** @test */
	public function it_can_store_request_data_even_when_clockwork_is_disabled() {
		$id = $this->get( '/' )
			// Headers should not be sent.
			->assert_header_missing( 'X-Clockwork-Id' )
			->assert_header_missing( 'X-Clockwork-Version' )
			->crawler()
			->filter( '#cfw-coh-clockwork-id' )
			->text();

		// API should not be accessible.
		$this->get( "/__clockwork/{$id}" )
			->assert_not_found();

		// But data should be stored.
		$this->assertSame( $id, static::api()->metadata_by_id( $id )['id'] );
	}
}
