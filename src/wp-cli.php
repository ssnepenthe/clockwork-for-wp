<?php

// @todo Probably better to use the WP-CLI namespace convention (space instead of colon).
\WP_CLI::add_command(
	'clockwork:clean',
	/**
	 * Cleans Clockwork request metadata.
	 *
	 * ## OPTIONS
	 *
	 * [--all]
	 * : Cleans all data.
	 *
	 * [--expiration=<minutes>]
	 * : Cleans data older than specified value in minutes. Does nothing if "--all" is also set.
	 */
	function( $_, $assoc_args ) {
		$config = \_cfw_instance()[ \Clockwork_For_Wp\Config::class ];

		if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'all', false ) ) {
			$config->set( 'storage.expiration', 0 );
		} else if ( \array_key_exists( 'expiration', $assoc_args ) ) {
			$config->set( 'storage.expiration', \abs( (int) $assoc_args['expiration'] ) );
		}

		\_cfw_instance()[ \Clockwork\Storage\StorageInterface::class ]->cleanup( $force = true );

		\WP_CLI::success( 'Metadata cleaned successfully.' );
	}
);
