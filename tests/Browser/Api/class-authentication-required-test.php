<?php

namespace Clockwork_For_Wp\Tests\Browser\Api;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Authentication_Required_Test extends Test_Case {
	const PASSWORD = 'nothing-to-see-here-folks';

	public function setUp(): void {
		parent::setUp();

		$this->with_config( [
			'authentication' => [
				'enabled' => true,
				'drivers' => [
					'simple' => [
						'config' => [
							'password' => static::PASSWORD,
						],
					],
				],
			],
		] );
	}

	/** @test */
	public function it_handles_json_auth_requests_with_invalid_json() {
		$bad_content = json_encode( [
			'username' => null,
			'password' => static::PASSWORD,
		] ) . '},';

		$this->post_json( '/__clockwork/auth', $bad_content )
			->assert_forbidden()
			->assert_json_path( 'token', false );
	}

	/** @test */
	public function it_handles_json_auth_requests_with_no_credentials() {
		$lack_of_creds = [
			'no' => 'username',
			'or' => 'password',
		];

		$this->post_json( '/__clockwork/auth', $lack_of_creds )
			->assert_forbidden()
			->assert_json_path( 'token', false );
	}

	/** @test */
	public function it_handles_json_auth_requests_with_incorrect_credentials() {
		$incorrect_creds = [
			'username' => null,
			'password' => 'password',
		];

		$this->post_json( '/__clockwork/auth', $incorrect_creds )
			->assert_forbidden()
			->assert_json_path( 'token', false );
	}

	/** @test */
	public function it_handles_json_auth_requests_with_correct_credentials() {
		$correct_creds = [
			'username' => null,
			'password' => static::PASSWORD,
		];

		$response = $this->post_json( '/__clockwork/auth', $correct_creds )
			->assert_ok()
			->assert_json_path( 'token', function( $value ) {
				// @todo Pattern match instead?
				$this->assertIsString( $value );
				$this->assertGreaterThanOrEqual( 60, strlen( $value ) );
			} );
	}

	/** @test */
	public function it_handles_form_auth_requests_with_no_credentials() {
		$lack_of_creds = [
			'no' => 'username',
			'or' => 'password',
		];

		$this->post( '/__clockwork/auth', $lack_of_creds )
			->assert_forbidden()
			->assert_json_path( 'token', false );
	}

	/** @test */
	public function it_handles_form_auth_requests_with_incorrect_credentials() {
		$incorrect_creds = [
			'username' => null,
			'password' => 'password',
		];

		$this->post( '/__clockwork/auth', $incorrect_creds )
			->assert_forbidden()
			->assert_json_path( 'token', false );
	}

	/** @test */
	public function it_handles_form_auth_requests_with_correct_credentials() {
		$correct_creds = [
			'username' => null,
			'password' => static::PASSWORD,
		];

		$this->post( '/__clockwork/auth', $correct_creds )
			->assert_ok()
			->assert_json_path( 'token', function( $value ) {
				// @todo Pattern match instead?
				$this->assertIsString( $value );
				$this->assertGreaterThanOrEqual( 60, strlen( $value ) );
			} );
	}

	/** @test */
	public function it_serves_forbidden_response_for_unauthenticated_requests() {
		$id = $this->get( '/' )
			->header( 'x-clockwork-id' );

		$this->get( "/__clockwork/{$id}" )
			->assert_forbidden();
	}

	/** @test */
	public function it_serves_ok_response_for_authenticated_requests() {
		$id = $this->get( '/' )
			->header( 'x-clockwork-id' );

		$response = $this->post( '/__clockwork/auth', [
			'username' => null,
			'password' => static::PASSWORD,
		] );

		$this->get( "/__clockwork/{$id}", [], [], [
			'HTTP_X_CLOCKWORK_AUTH' => $response->decode_response_json()['token'],
		] )
			->assert_ok();
	}
}
