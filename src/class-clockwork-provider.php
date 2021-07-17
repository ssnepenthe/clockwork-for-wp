<?php

namespace Clockwork_For_Wp;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Authentication\NullAuthenticator;
use Clockwork\Authentication\SimpleAuthenticator;
use Clockwork\Clockwork;
use Clockwork\Helpers\Serializer;
use Clockwork\Helpers\StackFilter;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Storage\FileStorage;
use Clockwork\Storage\SqlStorage;
use Clockwork\Storage\StorageInterface;
use Clockwork_For_Wp\Config;
use Clockwork_For_Wp\Data_Source\Php;

class Clockwork_Provider extends Base_Provider {
	public function boot() {
		if ( $this->plugin->is_collecting_data() ) {
			// Clockwork instance is resolved even when we are not collecting data in order to take
			// advantage of helper methods like shouldCollect and shouldRecord.
			// This ensures data sources are only registered on plugins_loaded when enabled.
			$this->add_data_sources();

			parent::boot();
		}
	}

	protected function add_data_sources() {
		$clockwork = $this->plugin[ Clockwork::class ];

		$clockwork->addDataSource( $this->plugin[ Php::class ] );

		foreach ( $this->plugin->get_enabled_data_sources() as $data_source ) {
			$clockwork->addDataSource( $this->plugin[ $data_source['data_source_class'] ] );
		}
	}

	public function register() {
		$this->plugin[ Clockwork_Subscriber::class ] = function() {
			return new Clockwork_Subscriber( $this->plugin );
		};

		$this->plugin[ Clockwork::class ] = function() {
			$clockwork = (new Clockwork())
				->authenticator( $this->plugin[ AuthenticatorInterface::class ] )
				->request( $this->plugin[ Request::class ] )
				->storage( $this->plugin[ StorageInterface::class ] );

			return $clockwork;
		};

		$this->plugin[ AuthenticatorInterface::class ] = function() {
			$config = $this->plugin[ Config::class ]->get( 'authentication', [] );

			if ( ! $config['enabled'] ?? false ) {
				return new NullAuthenticator();
			}

			$driver = $config['driver'] ?? 'simple';
			$factory_id = $config['drivers'][ $driver ]['class'] ?? SimpleAuthenticator::class;

			return $this->plugin[ $factory_id ]( $config['drivers'][ $driver ]['config'] ?? [] );
		};

		$this->plugin[ SimpleAuthenticator::class ] = $this->plugin->protect(
			function( array $config ) {
				if ( ! array_key_exists( 'password', $config ) ) {
					throw new \InvalidArgumentException( '@todo' );
				}

				return new SimpleAuthenticator( $config['password'] );
			}
		);

		$this->plugin[ StorageInterface::class ] = function() {
			$config = $this->plugin[ Config::class ]->get( 'storage', [] );
			$driver = $config['driver'] ?? 'file';
			$factory_id = $config['drivers'][ $driver ]['class'] ?? FileStorage::class;

			return $this->plugin[ $factory_id ]( $config['drivers'][ $driver ]['config'] ?? [] );
		};

		$this->plugin[ FileStorage::class ] = $this->plugin->protect( function( array $config ) {
			if ( ! array_key_exists( 'path', $config ) ) {
				throw new \InvalidArgumentException( '@todo' );
			}

			$dir_permissions = $config['dir_permissions'] ?? 0700;
			$expiration = $config['expiration'] ?? null;

			return new FileStorage( $config['path'], $dir_permissions, $expiration );
		} );

		$this->plugin[ SqlStorage::class ] = $this->plugin->protect( function( array $config ) {
			if ( ! array_key_exists( 'dsn', $config ) ) {
				throw new \InvalidArgumentException( '@todo' );
			}

			$table = $config['table'] ?? 'clockwork';
			$username = $config['username'] ?? null;
			$password = $config['password'] ?? null;
			$expiration = $config['expiration'] ?? null;

			return new SqlStorage( $config['dsn'], $table, $username, $password, $expiration );
		} );

		$this->plugin[ Log::class ] = function() {
			return new Log;
		};

		$this->plugin[ Request::class ] = function() {
			return new Request;
		};

		// Create request so we have id and start time available immediately.
		$this->plugin[ Request::class ];
		$this->configure_serializer();

		if ( $this->plugin->config( 'register_helpers', true ) ) {
			require_once __DIR__ . '/clock.php';
		}
	}

	protected function configure_serializer() {
		Serializer::defaults( [
			'limit' => $this->plugin->config( 'serialization.depth', 10 ),
			'blackbox' => $this->plugin->config( 'serialization.blackbox', [
				\Pimple\Container::class,
				\Pimple\Psr11\Container::class,
			] ),
			'traces' => $this->plugin->config( 'stack_traces.enabled', true ),
			'tracesSkip' => StackFilter::make()
				->isNotVendor( array_merge(
					$this->plugin->config( 'stack_traces.skip_vendors', [] ),
					[ 'itsgoingd' ]
				) )
				->isNotNamespace( $this->plugin->config( 'stack_traces.skip_namespaces', [] ) )
				->isNotFunction( [ 'call_user_func', 'call_user_func_array' ] )
				->isNotClass( $this->plugin->config( 'stack_traces.skip_classes', [] ) ),
			'tracesLimit' => $this->plugin->config( 'stack_traces.limit', 10 )
		] );
	}

	protected function subscribers() : array {
		return [ Clockwork_Subscriber::class ];
	}
}
