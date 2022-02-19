<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use Clockwork_For_Wp\Cli_Data_Collection\Cli_Collection_Helper;
use WP_CLI;

/**
 * @internal
 */
final class Generate_Command_List_Command extends Command {
	private $commands = [];

	public function configure(): void {
		$this->set_name( 'generate-command-list' )
			->set_description(
				'Generates list of core commands to be ignored by Clockwork. Used for development only.'
			);
	}

	public function handle(): void {
		$this->enumerate_commands( WP_CLI::get_root_command() );

		\file_put_contents(
			Cli_Collection_Helper::get_core_command_list_path(),
			\sprintf( $this->template(), \var_export( $this->commands, true ) )
		);

		WP_CLI::success( 'Successfully wrote command lists' );
	}

	private function enumerate_commands( $command, $parent = '' ): void {
		foreach ( $command->get_subcommands() as $subcommand ) {
			$command_string = empty( $parent )
				? $subcommand->get_name()
				: "{$parent} {$subcommand->get_name()}";

			if ( 0 !== \mb_strpos( $command_string, 'clockwork' ) ) {
				$this->commands[] = $command_string;
			}

			$this->enumerate_commands( $subcommand, $command_string );
		}
	}

	private function template() {
		$eol = \PHP_EOL;

		return "<?php{$eol}{$eol}// phpcs:ignoreFile{$eol}{$eol}return %s;{$eol}";
	}
}
