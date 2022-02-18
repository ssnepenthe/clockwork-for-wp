<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Authentication\NullAuthenticator;
use Clockwork\Authentication\SimpleAuthenticator;
use Clockwork\Clockwork;
use Clockwork\Helpers\Serializer;
use Clockwork\Helpers\StackFilter;
use Clockwork\Request\IncomingRequest;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Storage\FileStorage;
use Clockwork\Storage\SqlStorage;
use Clockwork\Storage\StorageInterface;
use Clockwork_For_Wp\Data_Source\Data_Source_Factory;
use InvalidArgumentException;

final class Clockwork_Provider extends Base_Provider {
	public function boot(): void {
		if ( $this->plugin->is_collecting_data() ) {
			// Clockwork instance is resolved even when we are not collecting data in order to take
			// advantage of helper methods like shouldCollect.
			// This ensures data sources are only registered on plugins_loaded when enabled.
			$this->add_data_sources();

			parent::boot();
		}
	}

	public function register(): void {
		$this->plugin[ Clockwork_Subscriber::class ] = function () {
			return new Clockwork_Subscriber( $this->plugin );
		};

		$this->plugin[ Clockwork::class ] = function () {
			return ( new Clockwork() )
				->authenticator( $this->plugin[ AuthenticatorInterface::class ] )
				->request( $this->plugin[ Request::class ] )
				->storage( $this->plugin[ StorageInterface::class ] );
		};

		$this->plugin[ AuthenticatorInterface::class ] = function () {
			$config = $this->plugin[ Config::class ]->get( 'authentication', [] );

			if ( ! ( $config['enabled'] ?? false ) ) {
				return new NullAuthenticator();
			}

			$driver = $config['driver'] ?? 'simple';
			$factory_id = $config['drivers'][ $driver ]['class'] ?? SimpleAuthenticator::class;

			return $this->plugin[ $factory_id ]( $config['drivers'][ $driver ]['config'] ?? [] );
		};

		$this->plugin[ SimpleAuthenticator::class ] = $this->plugin->protect(
			static function ( array $config ) {
				if ( ! \array_key_exists( 'password', $config ) ) {
					throw new InvalidArgumentException(
						'Missing "password" key from simple authenticator config array'
					);
				}

				return new SimpleAuthenticator( $config['password'] );
			}
		);

		$this->plugin[ StorageInterface::class ] = $this->plugin->factory(
			function () {
				$config = $this->plugin[ Config::class ]->get( 'storage', [] );
				$driver = $config['driver'] ?? 'file';
				$expiration = $config['expiration'] ?? null;
				$factory_id = $config['drivers'][ $driver ]['class'] ?? FileStorage::class;

				return $this->plugin[ $factory_id ](
					$config['drivers'][ $driver ]['config'] ?? [],
					$expiration
				);
			}
		);

		$this->plugin[ FileStorage::class ] = $this->plugin->protect(
			static function ( array $config, $expiration ) {
				if ( ! \array_key_exists( 'path', $config ) ) {
					throw new InvalidArgumentException(
						'Missing "path" key from file storage config array'
					);
				}

				$dir_permissions = $config['dir_permissions'] ?? 0700;
				$compress = $config['compress'] ?? false;

				return new FileStorage( $config['path'], $dir_permissions, $expiration, $compress );
			}
		);

		$this->plugin[ SqlStorage::class ] = $this->plugin->protect(
			static function ( array $config, $expiration ) {
				if ( ! \array_key_exists( 'dsn', $config ) ) {
					throw new InvalidArgumentException(
						'Missing "dns" key from sql storage config array'
					);
				}

				$table = $config['table'] ?? 'clockwork';
				$username = $config['username'] ?? null;
				$password = $config['password'] ?? null;

				return new SqlStorage( $config['dsn'], $table, $username, $password, $expiration );
			}
		);

		$this->plugin[ Log::class ] = static function () {
			return new Log();
		};

		$this->plugin[ Request::class ] = static function () {
			return new Request();
		};

		// Create request so we have id and start time available immediately.
		// Could probably even create it within Plugin::__construct() and save it to container.
		$this->plugin[ Request::class ];

		$this->plugin[ IncomingRequest::class ] = $this->plugin->factory(
			function () {
				return $this->plugin[ Incoming_Request::class ];
			}
		);

		$this->plugin[ Incoming_Request::class ] = static function () {
			return Incoming_Request::from_globals();
		};
	}

	public function registered(): void {
		$this->configure_serializer();
		$this->configure_should_collect();

		if ( $this->plugin->config( 'register_helpers', true ) ) {
			require_once __DIR__ . '/clock.php';
		}
	}

	protected function subscribers(): array {
		return [ Clockwork_Subscriber::class ];
	}

	private function add_data_sources(): void {
		$clockwork = $this->plugin[ Clockwork::class ];
		$data_source_factory = $this->plugin[ Data_Source_Factory::class ];

		$clockwork->addDataSource( $data_source_factory->create( 'php' ) );

		foreach ( $data_source_factory->get_enabled_data_sources() as $data_source ) {
			$clockwork->addDataSource( $data_source );
		}
	}

	private function configure_serializer(): void {
		Serializer::defaults(
			[
				'limit' => $this->plugin->config( 'serialization.depth', 10 ),
				'blackbox' => $this->plugin->config(
					'serialization.blackbox',
					[
						\Pimple\Container::class,
						\Pimple\Psr11\Container::class,
					]
				),
				'traces' => $this->plugin->config( 'stack_traces.enabled', true ),
				'tracesSkip' => StackFilter::make()
					->isNotVendor(
						\array_merge(
							$this->plugin->config( 'stack_traces.skip_vendors', [] ),
							[ 'itsgoingd' ]
						)
					)
					->isNotNamespace( $this->plugin->config( 'stack_traces.skip_namespaces', [] ) )
					->isNotFunction( [ 'call_user_func', 'call_user_func_array' ] )
					->isNotClass( $this->plugin->config( 'stack_traces.skip_classes', [] ) ),
				'tracesLimit' => $this->plugin->config( 'stack_traces.limit', 10 ),
			]
		);
	}

	private function configure_should_collect(): void {
		$should_collect = $this->plugin[ Clockwork::class ]->shouldCollect();

		$should_collect->merge(
			[
				'onDemand' => $this->plugin->config( 'requests.on_demand', false ),
				'sample' => $this->plugin->config( 'requests.sample', false ),
				'except' => $this->plugin->config( 'requests.except', [] ),
				'only' => $this->plugin->config( 'requests.only', [] ),
				'exceptPreflight' => $this->plugin->config( 'requests.except_preflight', true ),
			]
		);

		$should_collect->except( [ '/__clockwork(?:/.*)?' ] );
	}
}
