<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork\Storage\FileStorage;
use Clockwork\Storage\SqlStorage;
use Clockwork_For_Wp\Storage_Factory;
use InvalidArgumentException;
use Null_Storage_For_Tests;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class Storage_Factory_Test extends TestCase {
	/** @dataProvider provide_test_create */
	public function test_create( $name, $config, $class ) {
		$this->assertInstanceOf( $class, ( new Storage_Factory() )->create( $name, $config ) );
	}

	public function test_create_with_custom_factory() {
		$factory = new Storage_Factory();
		$factory->register_custom_factory( 'null', function() {
			return new Null_Storage_For_Tests();
		} );

		$this->assertInstanceOf( Null_Storage_For_Tests::class, $factory->create( 'null' ) );
	}

	public function test_create_with_custom_factory_override() {
		$factory = new Storage_Factory();
		$factory->register_custom_factory( 'file', function() {
			return new Null_Storage_For_Tests();
		} );

		$this->assertInstanceOf( Null_Storage_For_Tests::class, $factory->create( 'file' ) );
	}

	public function test_create_unsupported_storage() {
		$this->expectException( InvalidArgumentException::class );

		( new Storage_Factory() )->create( 'test' );
	}

	public function provide_test_create() {
		yield [
			'file',
			[
				'compress' => false,
				'dir_permissions' => 0700,
				'expiration' => null,
				'path' => vfsStream::setup()->url(),
			],
			FileStorage::class
		];

		yield [
			'sql',
			[
				'dsn' => $this->createMock( \PDO::class ),
				'expiration' => null,
				'password' => null,
				'table' => 'clockwork',
				'username' => null,
			],
			SqlStorage::class
		];
	}
}
