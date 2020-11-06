<?php

namespace Clockwork_For_Wp\Tests;

trait Manages_Metadata {
	public static function wp_with_test_commands( ...$args ) {
		$commands_file = realpath( __DIR__ . '/fixtures/commands.php' );
		$real_args = array_merge( $args, [ "--require={$commands_file}" ] );

		return Cli::wp( ...$real_args );
	}

	public static function clean_metadata() {
		return static::wp_with_test_commands( 'cfw-clean' )->mustRun()->getOutput();
	}

	public static function get_metadata_list() {
		return json_decode(
			static::wp_with_test_commands( 'cfw-list' )->mustRun()->getOutput(),
			true
		);
	}
}
