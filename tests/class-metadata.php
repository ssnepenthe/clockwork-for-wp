<?php

namespace Clockwork_For_Wp\Tests;

class Metadata {
	/**
	 * @return string
	 */
	public static function dir() {
		return \realpath( __DIR__ . '/../../../cfw-data' );
	}

	/**
	 * @return string[]
	 */
	public static function all() {
		return \glob( static::dir() . '/*.json' );
	}

	/**
	 * @return string[]
	 */
	public static function all_with_index() {
		$dir = static::dir();

		return \glob( "{{$dir}/*.json,{$dir}/index}", \GLOB_BRACE );
	}

	/**
	 * @return string|null
	 */
	public static function first() {
		$list = static::all();

		return \array_shift( $list );
	}

	/**
	 * @return string|null
	 */
	public static function last() {
		$list = static::all();

		return \array_pop( $list );
	}
}
