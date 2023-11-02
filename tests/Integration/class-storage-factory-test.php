<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork\Storage\FileStorage;
use Clockwork\Storage\SqlStorage;
use Clockwork_For_Wp\Storage_Factory;
use Clockwork_For_Wp\Tests\Creates_Config;
use InvalidArgumentException;
use Null_Storage_For_Tests;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use PDO;

class Storage_Factory_Test extends TestCase {
	use Creates_Config;

	/** @dataProvider provide_test_create */
	public function test_create( $name, $config, $class ): void {
		$this->assertInstanceOf( $class, ( new Storage_Factory() )->create( $name, $config ) );
	}

	public function test_create_does_not_cache_instances(): void {
		$factory = new Storage_Factory();
		$config = [
			'compress' => false,
			'dir_permissions' => 0700,
			'expiration' => null,
			'path' => vfsStream::setup()->url(),
		];

		$this->assertNotSame( $factory->create( 'file', $config ), $factory->create( 'file', $config ) );
	}

	public function test_create_with_custom_factory(): void {
		$factory = new Storage_Factory();
		$factory->register_custom_factory( 'null', fn() => new Null_Storage_For_Tests() );

		$this->assertInstanceOf( Null_Storage_For_Tests::class, $factory->create( 'null' ) );
	}

	public function test_create_with_custom_factory_override(): void {
		$factory = new Storage_Factory();
		$factory->register_custom_factory( 'file', fn() => new Null_Storage_For_Tests() );

		$this->assertInstanceOf( Null_Storage_For_Tests::class, $factory->create( 'file' ) );
	}

	public function test_create_unsupported_storage(): void {
		$this->expectException( InvalidArgumentException::class );

		( new Storage_Factory() )->create( 'test' );
	}

	/** @dataProvider provide_test_create_default */
	public function test_create_default( $config, $class, $expiration ): void {
		$factory = new Storage_Factory();
		$config = $this->create_config( $config );
		$storage = $factory->create_default( $config );

		// Not great but there are no getters on clockwork storage objects.
		// Would probably be better to register custom factory with class that extends clockwork storage and adds getter...
		$actual_expiration = ( function ( $storage ) {
			$r = new ReflectionProperty( $storage, 'expiration' );
			$r->setAccessible( true );

			return $r->getValue( $storage );
		} )( $storage );

		$this->assertInstanceOf( $class, $storage );
		$this->assertSame( $expiration, $actual_expiration );
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
			FileStorage::class,
		];

		yield [
			'sql',
			[
				'dsn' => $this->createMock( PDO::class ),
				'expiration' => null,
				'password' => null,
				'table' => 'clockwork',
				'username' => null,
			],
			SqlStorage::class,
		];
	}

	public function provide_test_create_default() {
		$config = fn( $d, $c ) => [
			'storage' => [
				'driver' => $d,
				'drivers' => [
					$d => $c,
				],
			],
		];

		$default_expiration = 60 * 24 * 7;

		// Default config uses file storage.
		yield [
			[
				'storage' => [
					'drivers' => [
						'file' => [
							'path' => vfsStream::setup()->url(),
						],
					],
				],
			],
			FileStorage::class,
			$default_expiration,
		];

		// Local expiration of null should fall back to global expiration.
		yield [
			$config( 'file', [
				'compress' => false,
				'dir_permissions' => 0700,
				'expiration' => null,
				'path' => vfsStream::setup()->url(),
			] ),
			FileStorage::class,
			$default_expiration,
		];

		yield [
			$config( 'sql', [
				'dsn' => $this->createMock( PDO::class ),
				'expiration' => null,
				'password' => null,
				'table' => 'clockwork',
				'username' => null,
			] ),
			SqlStorage::class,
			$default_expiration,
		];

		// Local expiration should override global expiration.
		yield [
			$config( 'file', [
				'compress' => false,
				'dir_permissions' => 0700,
				'expiration' => 10,
				'path' => vfsStream::setup()->url(),
			] ),
			FileStorage::class,
			10,
		];
	}
}
