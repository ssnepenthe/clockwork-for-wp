<?php

namespace Clockwork_For_Wp\Tests\Browser\Web_App;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Default_Test extends Test_Case {
	/** @test */
	public function it_prevents_trailing_slash_redirects() {
		// @todo Test with various permalink structures?
		$this->get( '/__clockwork/app' )
			->assert_ok();
		$this->get( '/__clockwork/assets/stylesheets/panel.css' )
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
			->assert_present( '[ng-app="Clockwork"]' )
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
		$this->get( '/__clockwork/assets/javascripts/app.js' )
			->assert_ok()
			->assert_header_starts_with( 'content-type', 'application/javascript' )
			->assert_header( 'content-length', function( $value ) {
				$this->assertIsString( $value );
				$this->assertIsNumeric( $value );
			} );
	}

	/** @test */
	public function it_correctly_serves_web_app_partials() {
		$this->get( '/__clockwork/assets/partials/stack-trace.html' )
			->assert_ok()
			->assert_present( '.stack-trace' )
			->assert_header_starts_with( 'content-type', 'text/html' )
			->assert_header( 'content-length', function( $value ) {
				$this->assertIsString( $value );
				$this->assertIsNumeric( $value );
			} );
	}

	/** @test */
	public function it_correctly_serves_web_app_styles() {
		// @todo Might want to consider blocking .scss files - panel.scss is currently available.
		$this->get( '/__clockwork/assets/stylesheets/panel.css' )
			->assert_ok()
			->assert_header_starts_with( 'content-type', 'text/css' )
			->assert_header( 'content-length', function( $value ) {
				$this->assertIsString( $value );
				$this->assertIsNumeric( $value );
			} );
	}

	/** @test */
	public function it_correctly_serves_web_app_vendor_fonts() {
		$this->get( '/__clockwork/assets/vendor/fonts/fontawesome-webfont.woff2' )
			->assert_ok()
			// @todo Fonts currently served with 'text/html' content type
			// ->assert_header_starts_with( 'content-type', 'INCORRECT' )
			->assert_header( 'content-length', function( $value ) {
				$this->assertIsString( $value );
				$this->assertIsNumeric( $value );
			} );
	}

	/** @test */
	public function it_correctly_serves_web_app_vendor_scripts() {
		$this->get( '/__clockwork/assets/vendor/javascripts/angular.min.js' )
			->assert_ok()
			->assert_header_starts_with( 'content-type', 'application/javascript' )
			->assert_header( 'content-length', function( $value ) {
				$this->assertIsString( $value );
				$this->assertIsNumeric( $value );
			} );
	}

	/** @test */
	public function it_correctly_serves_web_app_vendor_styles() {
		$this->get( '/__clockwork/assets/vendor/stylesheets/angular-csp.css' )
			->assert_ok()
			->assert_header_starts_with( 'content-type', 'text/css' )
			->assert_header( 'content-length', function( $value ) {
				$this->assertIsString( $value );
				$this->assertIsNumeric( $value );
			} );
	}

	/** @test */
	public function it_sends_404_for_invalid_files() {
		// @todo Also test nocache headers?
		$this->get( '/__clockwork/assets/nope.html' )
			->assert_not_found();
		$this->get( '/__clockwork/assets/images/nope.png' )
			->assert_not_found();
		$this->get( '/__clockwork/assets/javascripts/nope.js' )
			->assert_not_found();
		$this->get( '/__clockwork/assets/partials/nope.html' )
			->assert_not_found();
		$this->get( '/__clockwork/assets/stylesheets/nope.css' )
			->assert_not_found();
		$this->get( '/__clockwork/assets/vendor/fonts/nope.woff2' )
			->assert_not_found();
		$this->get( '/__clockwork/assets/vendor/javascript/nope.js' )
			->assert_not_found();
		$this->get( '/__clockwork/assets/vendor/stylesheets/nope.css' )
			->assert_not_found();
	}
}
