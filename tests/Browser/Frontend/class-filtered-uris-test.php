<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;
use Clockwork_For_Wp\Tests\Metadata;

class Filtered_Uris_Test extends Test_Case {
	public function setUp(): void {
		parent::setUp();

		$this->with_config( [
			'requests' => [
				'except' => [
					\Base64Url\Base64Url::encode( 'sample-page' ),
				],
			],
		] );
	}

	/** @test */
	public function it_does_not_send_clockwork_headers_for_filtered_uris() {
		$this->get( '/' )
			->assert_header( 'x-clockwork-id' )
			->assert_header( 'x-clockwork-version' );

		$this->get( '/sample-page/' )
			->assert_header_missing( 'x-clockwork-id' )
			->assert_header_missing( 'x-clockwork-version' );
	}

	/** @test */
	public function it_does_not_store_request_data_for_filtered_uris() {
		$this->get( '/sample-page/' );

		$this->assertCount( 0, Metadata::all() );

		$this->get( '/' );

		$this->assertCount( 1, Metadata::all() );
	}
}
