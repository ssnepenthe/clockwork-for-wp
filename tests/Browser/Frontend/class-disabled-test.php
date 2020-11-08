<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;
use Clockwork_For_Wp\Tests\Cli;

use function Clockwork_For_Wp\Tests\clean_metadata_files;
use function Clockwork_For_Wp\Tests\get_metadata_files_list;

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
		clean_metadata_files();

		$this->get( '/' );

		$this->assertCount( 0, get_metadata_files_list() );
	}
}


