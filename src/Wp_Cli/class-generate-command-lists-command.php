<?php

namespace Clockwork_For_Wp\Wp_Cli;

use WP_CLI;

class Generate_Command_Lists_Command extends Base_Command {
	protected $clockwork = [];
	protected $core = [];
	protected $description = 'Generates lists of commands that should be ignored by Clockwork. Used for development only.';
	protected $name = 'generate-command-lists';

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
		return \file_put_contents(
			__DIR__ . "/../Wp_Cli/{$which}-command-list.php",
			\sprintf( $this->template(), \var_export( $this->{$which}, true ) )
		);
	}
}
