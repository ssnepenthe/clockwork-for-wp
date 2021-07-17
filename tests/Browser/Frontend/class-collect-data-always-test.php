<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Collect_Data_Always_Test extends Test_Case {
	protected static function required_plugins() : array {
		return [ 'cfw-collect-data-always' ];
	}

	/** @test */
	public function it_can_store_request_data_even_when_clockwork_is_disabled() {
		$id = $this->get( '/' )
			// Headers should not be sent.
			->assert_header_missing( 'X-Clockwork-Id' )
			->assert_header_missing( 'X-Clockwork-Version' )
			->crawler()
			->filter( '.testing-clockwork-id' )
			->text();

		// API should not be accessible.
		$this->get( "/__clockwork/{$id}" )
			->assert_not_found();

		// But data should be stored.
		// By default data is stored in wp-content dir so let's just grab the actual json file...
		// Note that if you planned to use this plugin in production, the cfw data dir should be moved above the web root.
		$this->get( static::content_url() . "/cfw-data/{$id}.json" )
			->assert_ok()
			->assert_json_path( 'id', $id, true );
	}
}
