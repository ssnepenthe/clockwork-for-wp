<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use ApheleiaCli\Option;
use Clockwork_For_Wp\Configuration;
use Clockwork_For_Wp\Storage_Factory;
use WP_CLI;

/**
 * @internal
 */
final class Clean_Command extends Command {
	private Configuration $config;

	private Storage_Factory $storage_factory;

	public function __construct( Configuration $config, Storage_Factory $storage_factory ) {
		$this->config = $config;
		$this->storage_factory = $storage_factory;

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
		$driver = $this->config->get( 'storage.driver', 'file' );

		if ( $all ) {
			$this->config->set( "storage.drivers.{$driver}.expiration", 0 );
		} elseif ( null !== $expiration ) {
			// @todo Should we allow float?
			$this->config->set( "storage.drivers.{$driver}.expiration", \abs( (int) $expiration ) );
		}

		$this->storage_factory->create_default( $this->config->reader() )->cleanup( $force );

		// See https://github.com/itsgoingd/clockwork/issues/510
		// @todo Revisit after the release of Clockwork v6.
		if ( $all && 'file' === $driver ) {
			$path = $this->config->get( 'storage.drivers.file.path' );

			foreach ( \glob( $path . '/*.json' ) as $file ) {
				\unlink( $file );
			}
		}

		WP_CLI::success( 'Metadata cleaned successfully.' );
	}
}
