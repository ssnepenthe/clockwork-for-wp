<?php

namespace Clockwork_For_Wp;

use Clockwork\Storage\StorageInterface;
use Clockwork_For_Wp\Config;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Wp_Cli\Cli_Collection_Helper;
use WP_CLI;
use WP_CLI_Command;
use function WP_CLI\Utils\get_flag_value;

// @todo Seems like a pretty fragile implementation for collecting commands... Likely going to need a lot of work.

// @todo Probably better to use the WP-CLI namespace convention (space instead of colon).
WP_CLI::add_command(
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
		$config = \_cfw_instance()[ Config::class ];

		if ( get_flag_value( $assoc_args, 'all', false ) ) {
			$config->set( 'storage.expiration', 0 );
		} else if ( \array_key_exists( 'expiration', $assoc_args ) ) {
			// @todo Should we allow float?
			$config->set( 'storage.expiration', \abs( (int) $assoc_args['expiration'] ) );
		}

		\_cfw_instance()[ StorageInterface::class ]->cleanup( $force = true );

		WP_CLI::success( 'Metadata cleaned successfully.' );
	}
);

WP_CLI::add_command( 'clockwork:generate-command-lists', new class extends WP_CLI_Command {
	protected $clockwork = [];
	protected $core = [];

	public function __invoke() {
		$this->enumerate_commands( WP_CLI::get_root_command() );

		$this->write( 'clockwork' );
		$this->write( 'core' );

		WP_CLI::success( 'Successfully wrote command lists' );
	}

	protected function enumerate_commands( $command, $parent = '' ) {
		foreach ( $command->get_subcommands() as $subcommand ) {
			$command_string = empty( $parent )
				? $subcommand->get_name()
				: "{$parent} {$subcommand->get_name()}";

			if ( 0 === \strpos( $command_string, 'clockwork' ) ) {
				$this->clockwork[] = $command_string;
			} else {
				$this->core[] = $command_string;
			}

			$this->enumerate_commands( $subcommand, $command_string );
		}
	}

	protected function template() {
		$eol = PHP_EOL;

		return "<?php{$eol}{$eol}return %s;";
	}

	protected function write( $which ) {
		\file_put_contents(
			__DIR__ . "/Wp_Cli/{$which}-command-list.php",
			\sprintf( $this->template(), \var_export( $this->{$which}, true ) )
		);
	}
} );

$cli = new Cli_Collection_Helper( _cfw_instance()[ Event_Manager::class ] );
$cli->initialize_logger();

_cfw_instance()[ Cli_Collection_Helper::class ] = $cli;
