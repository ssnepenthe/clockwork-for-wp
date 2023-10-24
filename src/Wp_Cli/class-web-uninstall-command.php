<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use ApheleiaCli\Command;
use Clockwork_For_Wp\Plugin;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_CLI;

/**
 * @internal
 */
final class Web_Uninstall_Command extends Command {
	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		parent::__construct();
	}

	public function configure(): void {
		$this->setName( 'web-uninstall' )
			->setDescription( 'Uninstalls the Clockwork web app from the project web root' );
	}

	public function handle( $_, $assoc_args ): void {
		// @todo Use wp filesystem classes?
		if ( ! $this->plugin->is()->web_installed() ) {
			WP_CLI::error( 'Clockwork web app does not appear to be installed' );
		}

		$install_path = \get_home_path() . '__clockwork';
		$deleted_count = 0;

		WP_CLI::confirm(
			"Do you wish to remove {$install_path} and all files contained within?",
			$assoc_args
		);

		WP_CLI::debug( "Uninstalling Clockwork web app from {$install_path}", 'clockwork' );

		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $install_path, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $it as $file ) {
			$real_path = $file->getRealPath();

			if ( $file->isDir() ) {
				if ( ! \rmdir( $real_path ) ) {
					WP_CLI::error( "Unable to delete directory {$file->getPathName()}" );
				}

				WP_CLI::debug( "Deleted directory {$real_path}", 'clockwork' );
			} else {
				if ( ! \unlink( $real_path ) ) {
					WP_CLI::error( "Unable to delete file {$file->getPathName()}" );
				}

				WP_CLI::debug( "Deleted file {$real_path}", 'clockwork' );
			}

			$deleted_count++;
		}

		if ( ! \rmdir( $install_path ) ) {
			WP_CLI::error( "Unable to delete directory {$install_path}" );
		}

		WP_CLI::debug( "Deleted directory {$install_path}", 'clockwork' );

		$deleted_count++;

		WP_CLI::success( "Deleted {$deleted_count} files" );
	}
}
