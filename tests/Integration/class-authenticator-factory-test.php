<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Authentication\NullAuthenticator;
use Clockwork\Authentication\SimpleAuthenticator;
use Clockwork_For_Wp\Authenticator_Factory;
use Clockwork_For_Wp\Tests\Creates_Config;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class Authenticator_Factory_Test extends TestCase {
	use Creates_Config;

	/** @dataProvider provide_test_create */
	public function test_create( $name, $config, $class ) {
		$this->assertInstanceOf(
			$class,
			( new Authenticator_Factory() )->create( $name, $config )
		);
	}

	public function test_create_does_not_cache_instances() {
		$factory = new Authenticator_Factory();

		$this->assertNotSame( $factory->create( 'null' ), $factory->create( 'null' ) );
	}

	public function test_create_with_custom_factory() {
		$authenticator = new class implements AuthenticatorInterface {
			public function attempt( array $credentials ) {}
			public function check( $token ) {}
			public function requires() {}
		};
		$factory = new Authenticator_Factory();
		$factory->register_custom_factory( 'test', function() use ( $authenticator ) {
			return $authenticator;
		} );

		$this->assertSame( $authenticator, $factory->create( 'test' ) );
	}

	public function test_create_with_custom_factory_override() {
		$factory = new Authenticator_Factory();
		$factory->register_custom_factory( 'simple', function() {
			return new NullAuthenticator();
		} );

		$this->assertInstanceOf( NullAuthenticator::class, $factory->create( 'simple' ) );
	}

	public function test_create_unsupported_authenticator() {
		$this->expectException( InvalidArgumentException::class );

		( new Authenticator_Factory() )->create( 'test' );
	}

	/** @dataProvider provide_test_create_default */
	public function test_create_default( $config, $class, $password ) {
		$factory = new Authenticator_Factory();
		$config = $this->create_config( $config );
		$authenticator = $factory->create_default( $config );

		$this->assertInstanceOf( $class, $authenticator );

		// Attempt returns false on failure, true or the hashed password on success.
		$attempt = $authenticator->attempt( [ 'password' => $password ] );
		$this->assertTrue( true === $attempt || is_string( $attempt ) );
	}

	public function provide_test_create() {
		yield [ 'null', [], NullAuthenticator::class ];
		yield [ 'simple', [ 'password' => 'irrelevant' ], SimpleAuthenticator::class ];
	}

	public function provide_test_create_default() {
		yield [ [], NullAuthenticator::class, '' ];

		yield [
			[
				'authentication' => [
					'enabled' => false,
				],
			],
			NullAuthenticator::class,
			'',
		];

		$password = 'testpassword';

		yield [
			[
				'authentication' => [
					'enabled' => true,
					'driver' => 'simple',
					'drivers' => [
						'simple' => [
							'password' => $password,
						],
					],
				],
			],
			SimpleAuthenticator::class,
			$password,
		];
	}
}
