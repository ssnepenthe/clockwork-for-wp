<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;
use Clockwork_For_Wp\Tests\Cli;
use Clockwork_For_Wp\Tests\Manages_Metadata;

class Disabled_Test extends Test_Case {
	use Manages_Metadata;

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
		static::clean_metadata();

		$this->get( '/' );

		$this->assertCount( 0, static::get_metadata_list() );
	}
}


