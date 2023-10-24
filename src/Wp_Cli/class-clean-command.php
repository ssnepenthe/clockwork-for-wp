<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use ApheleiaCli\Option;
use Clockwork\Storage\StorageInterface;
use League\Config\ConfigurationBuilderInterface;
use League\Config\ConfigurationInterface;
use Pimple\Container;
use WP_CLI;

/**
 * @internal
 */
final class Clean_Command extends Command {
	private Container $container;

	public function __construct( Container $container ) {
		$this->container = $container;

		parent::__construct();
	}

	public function configure(): void {
		$this->setName( 'clean' )
			->setDescription( 'Cleans Clockwork request metadata.' )
			->addFlag(
				( new Flag( 'all' ) )
					->setDescription( 'Cleans all data.' )
			)
			->addOption(
				( new Option( 'expiration' ) )
					->setDescription( 'Cleans data older than specified value in minutes. Does nothing if "--all" is also set.' )
			);
	}

	public function handle( $_, $assoc_args ): void {
		$force = true;
		$all = $assoc_args['all'] ?? false;
		$expiration = $assoc_args['expiration'] ?? null;

		if ( $all ) {
			$this->container[ ConfigurationBuilderInterface::class ]->set( 'storage.expiration', 0 );
		} elseif ( null !== $expiration ) {
			// @todo Should we allow float?
			$this->container[ ConfigurationBuilderInterface::class ]->set(
				'storage.expiration',
				\abs( (int) $expiration )
			);
		}

		$this->container[ StorageInterface::class ]->cleanup( $force );

		// See https://github.com/itsgoingd/clockwork/issues/510
		// @todo Revisit after the release of Clockwork v6.
		$config = $this->container[ ConfigurationInterface::class ];

		if ( $all && 'file' === $config->get( 'storage.driver', 'file' ) ) {
			$path = $config->get( 'storage.drivers.file.path' );

			foreach ( \glob( $path . '/*.json' ) as $file ) {
				\unlink( $file );
			}
		}

		WP_CLI::success( 'Metadata cleaned successfully.' );
	}
}
