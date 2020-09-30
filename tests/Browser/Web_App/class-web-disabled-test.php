<?php

namespace Clockwork_For_Wp\Tests\Browser\Web_App;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Web_Disabled_Test extends Test_Case {
	protected static function required_plugins() : array {
		return [ 'cfw-web-disabled' ];
	}

	/** @test */
	public function it_serves_not_found_response_for_app_requests() {
		$this->get( '/__clockwork/app' )
			->assert_not_found();
	}

	/** @test */
	public function it_serves_not_found_response_for_assets_requests() {
		$this->get( '/__clockwork/assets/stylesheets/panel.css' )
			->assert_not_found();
	}

	/** @test */
	public function it_does_not_provide_a_shortcut_redirect() {
		$this->get( '/__clockwork' )
			->assert_not_found();
	}

	/** @test */
	public function it_does_not_prevent_trailing_slash_redirects() {
		$this->markTestIncomplete( 'Not yet implemented' );
	}
}
