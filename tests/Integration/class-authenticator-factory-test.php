<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Authentication\NullAuthenticator;
use Clockwork\Authentication\SimpleAuthenticator;
use Clockwork_For_Wp\Authenticator_Factory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class Authenticator_Factory_Test extends TestCase {
	/** @dataProvider provide_test_create */
	public function test_create( $name, $config, $class ) {
		$this->assertInstanceOf(
			$class,
			( new Authenticator_Factory() )->create( $name, $config )
		);
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

	public function provide_test_create() {
		yield [ 'null', [], NullAuthenticator::class ];
		yield [ 'simple', [ 'password' => 'irrelevant' ], SimpleAuthenticator::class ];
	}
}
