<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use WP_CLI;

final class Generate_Command_Lists_Command extends Base_Command {
	protected $description = 'Generates lists of commands that should be ignored by Clockwork. Used for development only.';
	protected $name = 'generate-command-lists';
	private $clockwork = [];
	private $core = [];

	public function __invoke(): void {
		$this->enumerate_commands( WP_CLI::get_root_command() );

		$this->write( 'clockwork' );
		$this->write( 'core' );

		WP_CLI::success( 'Successfully wrote command lists' );
	}

	private function enumerate_commands( $command, $parent = '' ): void {
		foreach ( $command->get_subcommands() as $subcommand ) {
			$command_string = empty( $parent )
				? $subcommand->get_name()
				: "{$parent} {$subcommand->get_name()}";

			if ( 0 === \mb_strpos( $command_string, 'clockwork' ) ) {
				$this->clockwork[] = $command_string;
			} else {
				$this->core[] = $command_string;
			}

			$this->enumerate_commands( $subcommand, $command_string );
		}
	}

	private function template() {
		$eol = \PHP_EOL;

		return "<?php{$eol}{$eol}// phpcs:ignoreFile{$eol}{$eol}return %s;{$eol}";
	}

	private function write( $which ) {
		return \file_put_contents(
			__DIR__ . "/../Cli_Data_Collection/{$which}-command-list.php",
			\sprintf( $this->template(), \var_export( $this->{$which}, true ) )
		);
	}
}
