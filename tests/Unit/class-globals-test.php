<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Tests\Unit;

use Clockwork_For_Wp\Globals;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class Globals_Test extends TestCase {
	public const GLOBAL_KEY = 'standard_global';

	public const GLOBAL_VALUE = 'standard value';

	protected function tearDown(): void {
		unset( $GLOBALS[ self::GLOBAL_KEY ] );

		Globals::reset();
	}

	public function test_get(): void {
		$GLOBALS[ self::GLOBAL_KEY ] = self::GLOBAL_VALUE;

		$this->assertSame( self::GLOBAL_VALUE, Globals::get( self::GLOBAL_KEY ) );
	}

	public function test_get_throws_when_global_not_set(): void {
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Global variable "var_does_not_exist" is not set' );

		Globals::get( 'var_does_not_exist' );
	}

	public function test_get_with_default(): void {
		$value = 'default value';

		Globals::set_default( 'with_default', $value );

		$this->assertSame( $value, Globals::get( 'with_default' ) );
	}

	public function test_get_with_getter(): void {
		$value = 'getter value';

		Globals::set_getter( 'with_getter', static fn() => $value );

		$this->assertSame( $value, Globals::get( 'with_getter' ) );
	}

	public function test_get_with_initializer(): void {
		$value = 'initializer value';

		Globals::set_initializer( 'with_initializer', static function () use ( $value ): void {
			$GLOBALS['with_initializer'] = $value;
		} );

		$this->assertSame( $value, Globals::get( 'with_initializer' ) );
	}

	public function test_safe_get_returns_default_instead_of_throwing(): void {
		$default = 'just a default value for testing...';

		$this->assertNull( Globals::safe_get( 'var_does_not_exist' ) );
		$this->assertSame( $default, Globals::safe_get( 'var_does_not_exist', $default ) );
	}
}
