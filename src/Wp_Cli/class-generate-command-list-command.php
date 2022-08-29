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
		$root_command = WP_CLI::runcommand(
			'cli cmd-dump --skip-packages --skip-plugins --skip-themes',
			[
				'parse' => 'json',
				'return' => true,
			]
		);

		$this->add_command_to_commands_list( $root_command );

		\file_put_contents(
			Cli_Collection_Helper::get_core_command_list_path(),
			\sprintf( $this->template(), \var_export( $this->commands, true ) )
		);

		WP_CLI::success( 'Successfully wrote command lists' );
	}

	private function add_command_to_commands_list( $command, $parent = '' ): void {
		if ( \array_key_exists( 'subcommands', $command ) ) {
			foreach ( $command['subcommands'] as $subcommand ) {
				$command_string = empty( $parent )
					? $subcommand['name']
					: "{$parent} {$subcommand['name']}";

				$this->commands[] = $command_string;

				$this->add_command_to_commands_list( $subcommand, $command_string );
			}
		}
	}

	private function template() {
		$eol = \PHP_EOL;

		return "<?php{$eol}{$eol}// phpcs:ignoreFile{$eol}{$eol}return %s;{$eol}";
	}
}
