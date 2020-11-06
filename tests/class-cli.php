<?php

namespace Clockwork_For_Wp\Tests;

use Symfony\Component\Process\Exception\ExceptionInterface;
use Symfony\Component\Process\Process;

class Cli {
	protected static $wp_bin_path;

	public static function process( string ...$args ) : Process {
		return new Process( $args );
	}

	public static function wp( string ...$args ) : Process {
		return static::process( static::get_wp_bin_path(), ...$args );
	}

	public static function get_wp_bin_path() : string {
		if ( null === static::$wp_bin_path ) {
			try {
				// @todo Something more portable?
				$wp = trim( static::process( 'which', 'wp' )->mustRun()->getOutput() );
			} catch ( ExceptionInterface $e ) {
				// @todo Better to just let it throw?
				$wp = 'wp';
			}

			static::$wp_bin_path = $wp;
		}

		return static::$wp_bin_path;
	}

	public static function set_wp_bin_path( string $path ) : void {
		static::$wp_bin_path = $path;
	}
}
