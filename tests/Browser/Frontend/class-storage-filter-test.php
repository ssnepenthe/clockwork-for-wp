<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Storage_Filter_Test extends Test_Case {
	protected static function required_plugins() : array {
		return [ 'cfw-storage-filter' ];
	}

	/** @test */
	public function it_correctly_applies_clockwork_storage_filter() {
		$id = $this->get( '/' )
			->header( 'x-clockwork-id' );

		$this->get( "/__clockwork/{$id}" )
			->assert_json_path( 'url', null );
	}
}
