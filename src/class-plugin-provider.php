<?php

namespace Clockwork_For_Wp;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Authentication\NullAuthenticator;
use Clockwork\Authentication\SimpleAuthenticator;
use Clockwork\Clockwork;
use Clockwork\Storage\FileStorage;
use Clockwork\Storage\SqlStorage;
use Clockwork\Storage\StorageInterface;
use Clockwork_For_Wp\Data_Source\Php;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Pimple\Psr11\Container;

class Plugin_Provider extends Base_Provider {
	public function boot() {
		// Ensures Clockwork is instantiated and all data sources are added on 'plugins_loaded'.
		$this->plugin[ Clockwork::class ]; // @todo Move into conditional?

		if ( $this->plugin->is_collecting_data() ) {
			parent::boot();
		}
	}

	public function register() {
		$this->plugin[ Plugin::class ] = $this->plugin; // @todo Does this work?

		$this->plugin[ Plugin_Subscriber::class ] = function() {
			return new Plugin_Subscriber( $this->plugin );
		};

		$this->plugin[ Config::class ] = function() {
			$values = include __DIR__ . '/config.php';
			$config = new Config( $values );

			$this->plugin[ Event_Manager::class ]->trigger( 'cfw_config_init', $config );

			return $config;
		};

		$this->plugin[ Invoker::class ] = function() {
			$psr_container = new Container( $this->plugin->get_container() );

			return new Invoker(
				new ResolverChain( [
					new TypeHintContainerResolver( $psr_container ),
					new ParameterNameContainerResolver( $psr_container ),
					new NumericArrayResolver(),
					new AssociativeArrayResolver(),
					new DefaultValueResolver(),
				] ),
				$psr_container
			);
		};

		// @todo Separate clockwork provider?
		$this->plugin[ Clockwork::class ] = function() {
			$clockwork = new Clockwork();

			$clockwork->addDataSource( $this->plugin[ Php::class ] );

			foreach ( $this->plugin->get_enabled_data_sources() as $data_source ) {
				$clockwork->addDataSource( $this->plugin[ $data_source['data_source_class'] ] );
			}

			$clockwork->setAuthenticator( $this->plugin[ AuthenticatorInterface::class ] );
			$clockwork->setStorage( $this->plugin[ StorageInterface::class ] );

			return $clockwork;
		};

		$this->register_authenticator();
		$this->register_storage();
	}

	protected function register_authenticator() {
		$this->plugin[ AuthenticatorInterface::class ] = function() {
			$config = $this->plugin[ Config::class ]->get( 'authentication' );

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
	}

	protected function register_storage() {
		$this->plugin[ StorageInterface::class ] = function() {
			$config = $this->plugin[ Config::class ]->get( 'storage' );
			$driver = $config['driver'] ?? 'file';
			$factory_id = $config['drivers'][ $driver ]['class'] ?? FileStorage::class;

			$storage = $this->plugin[ $factory_id ](
				$config['drivers'][ $driver ]['config'] ?? []
			);

			return $storage;
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
	}

	protected function subscribers() : array {
		return [ Plugin_Subscriber::class ];
	}
}
