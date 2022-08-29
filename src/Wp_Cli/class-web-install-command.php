<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use Clockwork\Web\Web;
use Clockwork_For_Wp\Plugin;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_CLI;

/**
 * @internal
 */
final class Web_Install_Command extends Command {
	public function configure(): void {
		$this->set_name( 'web-install' )
			->set_description( 'Installs the Clockwork web app to the project web root' );
	}

	public function handle( Plugin $plugin ): void {
		// @todo Use wp filesystem classes?
		// @todo Force/overwrite option?
		if ( $plugin->is_web_installed() ) {
			WP_CLI::error(
				'Clockwork web app is already installed'
				. ' - If you want to reinstall please run clockwork web-uninstall first'
			);
		}

		$source_path = \dirname( (new Web())->asset( 'index.html' )['path'] );
		$destination_path = \get_home_path() . '__clockwork';
		$installed_count = 0;

		WP_CLI::log( "Source path: {$source_path}" );
		WP_CLI::log( "Destination path: {$destination_path}" );
		WP_CLI::log( '' );

		if ( \file_exists( $destination_path ) ) {
			// @todo Prompt to delete/overwrite?
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
