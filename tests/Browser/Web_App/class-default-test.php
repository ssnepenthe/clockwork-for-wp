<?php

namespace Clockwork_For_Wp\Tests\Browser\Web_App;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Default_Test extends Test_Case {
	/** @test */
	public function it_prevents_trailing_slash_redirects() {
		// @todo Test with various permalink structures?
		$this->get( '/__clockwork/app' )
			->assert_ok();
		$this->get( '/__clockwork/index.html' )
			->assert_ok();
	}

	/** @test */
	public function it_provides_a_shortcut_redirect() {
		// @todo Test for 302 specifically?
		$this->get( '/__clockwork' )
			->assert_redirect()
			->follow_redirects()
			->assert_url_ends_with( '/__clockwork/app' );
	}

	/** @test */
	public function it_correctly_serves_web_app_index() {
		$response = $this->get( '/__clockwork/app' )
			->assert_ok()
			->assert_present( '[id="app"]' )
			->assert_header_starts_with( 'content-type', 'text/html' );

		// Content-length will be removed if transfer-encoding is set.
		// @todo This eventually needs to be tested unconditionally.
		if ( null !== $content_length = $response->header( 'content-length' ) ) {
			$this->assertIsNumeric( $content_length );
		}
	}

	/** @test */
	public function it_correctly_serves_web_app_images() {
		// @todo Currently fails - serves the image but with text/html content type.
		// Ultimately this should probably 404 - images are only used in browser extension manifest.
		$this->markTestIncomplete( 'Not yet implemented' );
	}

	/** @test */
	public function it_correctly_serves_web_app_scripts() {
		// app.{hash}.js
		$path = $this->get( '/__clockwork/app' )
			->crawler()
			->filter( 'script' )
			->last()
			->attr( 'src' );

		$response = $this->get( "/__clockwork/{$path}" )
			->assert_ok()
			->assert_header_starts_with( 'content-type', 'application/javascript' );

		// Content-length will be removed if transfer-encoding is set.
		// @todo This eventually needs to be tested unconditionally.
		if ( null !== $content_length = $response->header( 'content-length' ) ) {
			$this->assertIsNumeric( $content_length );
		}
	}

	/** @test */
	public function it_correctly_serves_web_app_styles() {
		// app.{hash}.css
		$path = $this->get( '/__clockwork/app' )
			->crawler()
			->filter( 'link' )
			->first()
			->attr( 'href' );

		$response = $this->get( "/__clockwork/{$path}" )
			->assert_ok()
			->assert_header_starts_with( 'content-type', 'text/css' );

		// Content-length will be removed if transfer-encoding is set.
		// @todo This eventually needs to be tested unconditionally.
		if ( null !== $content_length = $response->header( 'content-length' ) ) {
			$this->assertIsNumeric( $content_length );
		}
	}

	/** @test */
	public function it_correctly_serves_web_app_vendor_scripts() {
		// chunk-vendors.{hash}.js
		$path = $this->get( '/__clockwork/app' )
			->crawler()
			->filter( 'script' )
			->first()
			->attr( 'src' );

		$response = $this->get( "/__clockwork/{$path}" )
			->assert_ok()
			->assert_header_starts_with( 'content-type', 'application/javascript' );

		// Content-length will be removed if transfer-encoding is set.
		// @todo This eventually needs to be tested unconditionally.
		if ( null !== $content_length = $response->header( 'content-length' ) ) {
			$this->assertIsNumeric( $content_length );
		}
	}

	/** @test */
	public function it_sends_404_for_invalid_files() {
		// @todo Also test nocache headers?
		$this->get( '/__clockwork/nope.html' )
			->assert_not_found();
		$this->get( '/__clockwork/img/nope.png' )
			->assert_not_found();
		$this->get( '/__clockwork/js/nope.js' )
			->assert_not_found();
		$this->get( '/__clockwork/css/nope.css' )
			->assert_not_found();
	}
}
