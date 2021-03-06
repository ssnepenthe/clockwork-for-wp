<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Additional_Headers_Test extends Test_Case {
	protected static function required_plugins() : array {
		return [ 'cfw-additional-headers' ];
	}

	/** @test */
	public function it_correctly_sends_configured_headers() {
		$this->get( '/' )
			->assert_header( 'x-clockwork-header-apples', 'Bananas' )
			->assert_header( 'x-clockwork-header-cats', 'Dogs' );
	}

	/** @test */
	public function it_does_not_send_additional_headers_on_filtered_uris() {
		$this->get( '/sample-page/' )
			->assert_header_missing( 'x-clockwork-header-apples' )
			->assert_header_missing( 'x-clockwork-header-cats' );
	}
}
