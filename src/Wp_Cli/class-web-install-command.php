<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use Clockwork\Web\Web;
use Clockwork_For_Wp\Plugin;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_CLI;

/**
 * @internal
 */
final class Web_Install_Command extends Command {
	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		parent::__construct();
	}

	public function configure(): void {
		$this->setName( 'web-install' )
			->setDescription( 'Installs the Clockwork web app to the project web root' )
			->addFlag(
				( new Flag( 'force' ) )
					->setDescription( 'Uninstall web app if it is already installed' )
			);
	}

	public function handle( $_, $assoc_args ): void {
		$force = $assoc_args['force'] ?? false;

		// @todo Use wp filesystem classes?
		if ( $this->plugin->is_web_installed() ) {
			if ( $force ) {
				WP_CLI::log( 'Removing previous Clockwork web app installation...' );
				WP_CLI::debug( 'Running "clockwork web-uninstall"', 'clockwork' );

				WP_CLI::runcommand( 'clockwork web-uninstall --yes' );
			} else {
				WP_CLI::error(
					'Clockwork web app is already installed - Please re-run with the "--force" flag'
					. ' or manually run "clockwork web-uninstall"'
				);
			}
		}

		$source_path = \dirname( ( new Web() )->asset( 'index.html' )['path'] );
		$destination_path = \get_home_path() . '__clockwork';
		$installed_count = 0;

		WP_CLI::debug( 'Installing Clockwork web app', 'clockwork' );
		WP_CLI::debug( "Source path: {$source_path}", 'clockwork' );
		WP_CLI::debug( "Destination path: {$destination_path}", 'clockwork' );

		if ( \file_exists( $destination_path ) ) {
			// @todo Prompt to delete/overwrite? Or overwrite flag?
			WP_CLI::error(
				'Destination path already exists but does not contain a Clockwork web app install'
				. ' - please delete manually and run clockwork web-install again'
			);
		}

		\wp_mkdir_p( $destination_path );

		WP_CLI::debug( "Created directory {$destination_path}", 'clockwork' );

		$installed_count++;

		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $source_path, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $it as $file ) {
			$inner = $it->getInnerIterator();
			$destination_file_path = "{$destination_path}/{$inner->getSubPathName()}";

			if ( $file->isDir() ) {
				if ( ! \wp_mkdir_p( $destination_file_path ) ) {
					WP_CLI::error( "Unable to create directory {$destination_file_path}" );
				}

				WP_CLI::debug( "Created directory {$destination_file_path}", 'clockwork' );
			} else {
				if ( ! \copy( $file->getRealPath(), $destination_file_path ) ) {
					WP_CLI::error( "Unable to copy file {$destination_file_path}" );
				}

				WP_CLI::debug(
					"Copied file {$inner->getSubPathName()} to {$destination_file_path}",
					'clockwork'
				);
			}

			$installed_count++;
		}

		WP_CLI::success( "Installed {$installed_count} files" );
	}
}
