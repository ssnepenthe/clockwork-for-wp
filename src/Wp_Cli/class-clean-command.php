<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use ApheleiaCli\Option;
use Clockwork\Storage\StorageInterface;
use League\Config\ConfigurationBuilderInterface;
use League\Config\ConfigurationInterface;
use WP_CLI;

/**
 * @internal
 */
final class Clean_Command extends Command {
	public function configure(): void {
		$this->setName( 'clean' )
			->setDescription( 'Cleans Clockwork request metadata.' )
			->addFlag(
				( new Flag( 'all' ) )
					->setDescription( 'Cleans all data.' )
			)
			->addOption(
				( new Option( 'expiration' ) )
					->setDescription(
						'Cleans data older than specified value in minutes. Does nothing if "--all" is also set.'
					)
			);
	}

	public function handle( $all = false, $expiration = null ): void {
		$force = true;
		$container = \_cfw_instance()->getContainer();

		if ( $all ) {
			$container->get( ConfigurationBuilderInterface::class )->set( 'storage.expiration', 0 );
		} elseif ( null !== $expiration ) {
			// @todo Should we allow float?
			$container->get( ConfigurationBuilderInterface::class )->set(
				'storage.expiration',
				\abs( (int) $expiration )
			);
		}

		$container->get( StorageInterface::class )->cleanup( $force );

		// See https://github.com/itsgoingd/clockwork/issues/510
		// @todo Revisit after the release of Clockwork v6.
		$config = $container->get( ConfigurationInterface::class );

		if ( $all && 'file' === $config->get( 'storage.driver', 'file' ) ) {
			$path = $config->get( 'storage.drivers.file.path' );

			foreach ( \glob( $path . '/*.json' ) as $file ) {
				\unlink( $file );
			}
		}

		WP_CLI::success( 'Metadata cleaned successfully.' );
	}
}
