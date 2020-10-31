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
		$this->get( '/__clockwork/app' )
			->assert_ok()
			->assert_present( '[id="app"]' )
			->assert_header_starts_with( 'content-type', 'text/html' )
			->assert_header( 'content-length', function( $value ) {
				$this->assertIsString( $value );
				$this->assertIsNumeric( $value );
			} );
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

		$this->get( "/__clockwork/{$path}" )
			->assert_ok()
			->assert_header_starts_with( 'content-type', 'application/javascript' )
			->assert_header( 'content-length', function( $value ) {
				$this->assertIsString( $value );
				$this->assertIsNumeric( $value );
			} );
	}

	/** @test */
	public function it_correctly_serves_web_app_styles() {
		// app.{hash}.css
		$path = $this->get( '/__clockwork/app' )
			->crawler()
			->filter( 'link' )
			->first()
			->attr( 'href' );

		$this->get( "/__clockwork/{$path}" )
			->assert_ok()
			->assert_header_starts_with( 'content-type', 'text/css' )
			->assert_header( 'content-length', function( $value ) {
				$this->assertIsString( $value );
				$this->assertIsNumeric( $value );
			} );
	}

	/** @test */
	public function it_correctly_serves_web_app_vendor_scripts() {
		// chunk-vendors.{hash}.js
		$path = $this->get( '/__clockwork/app' )
			->crawler()
			->filter( 'script' )
			->first()
			->attr( 'src' );

		$this->get( "/__clockwork/{$path}" )
			->assert_ok()
			->assert_header_starts_with( 'content-type', 'application/javascript' )
			->assert_header( 'content-length', function( $value ) {
				$this->assertIsString( $value );
				$this->assertIsNumeric( $value );
			} );
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
