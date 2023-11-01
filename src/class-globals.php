<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use RuntimeException;

final class Globals {
	private static array $defaults = [
		'content_width' => 0,
	];

	private static array $getters = [
		'wp_rest_server' => 'rest_get_server',
	];

	private static array $initializers = [
		'wp_object_cache' => [ self::class, 'initialize_wp_object_cache' ],
	];

	public static function get( string $name ) {
		if ( \array_key_exists( $name, self::$getters ) ) {
			return ( self::$getters[ $name ] )();
		}

		if ( ! \array_key_exists( $name, $GLOBALS ) && \array_key_exists( $name, self::$initializers ) ) {
			( self::$initializers[ $name ] )();
		}

		if ( \array_key_exists( $name, $GLOBALS ) ) {
			return $GLOBALS[ $name ];
		}

		if ( \array_key_exists( $name, self::$defaults ) ) {
			return self::$defaults[ $name ];
		}

		throw new RuntimeException( "Global variable \"{$name}\" is not set" );
	}

	public static function reset(): void {
		self::$defaults = [
			'content_width' => 0,
		];

		self::$getters = [
			'wp_rest_server' => 'rest_get_server',
		];

		self::$initializers = [
			'wp_object_cache' => [ self::class, 'initialize_wp_object_cache' ],
		];
	}

	public static function safe_get( string $name, $default = null ) {
		try {
			return self::get( $name );
		} catch ( RuntimeException $e ) {
			return $default;
		}
	}

	public static function set_default( string $name, $default ): void {
		self::$defaults[ $name ] = $default;
	}

	public static function set_getter( string $name, callable $getter ): void {
		self::$getters[ $name ] = $getter;
	}

	public static function set_initializer( string $name, callable $initializer ): void {
		self::$initializers[ $name ] = $initializer;
	}

	private static function initialize_wp_object_cache(): void {
		\function_exists( 'wp_cache_init' ) && \wp_cache_init();
	}
}
