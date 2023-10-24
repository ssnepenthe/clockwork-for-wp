<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Clockwork;
use Clockwork\Helpers\Serializer;
use Clockwork\Helpers\StackFilter;
use Clockwork\Request\IncomingRequest;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Request\ShouldCollect;
use Clockwork\Storage\StorageInterface;
use Clockwork_For_Wp\Data_Source\Data_Source_Factory;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Pimple\Container;

/**
 * @internal
 */
final class Clockwork_Provider extends Base_Provider {
	public function boot( Plugin $plugin ): void {
		if ( $plugin->is()->collecting_data() ) {
			$pimple = $plugin->get_pimple();

			// Clockwork instance is resolved even when we are not collecting data in order to take
			// advantage of helper methods like shouldCollect.
			// This ensures data sources are only registered on plugins_loaded when enabled.
			$pimple[ Clockwork_Support::class ]->add_data_sources();

			$pimple[ Event_Manager::class ]->attach(
				new Clockwork_Subscriber(
					$pimple[ Read_Only_Configuration::class ],
					$pimple[ Plugin::class ]->is(),
					$pimple[ Event_Manager::class ],
					$pimple[ Clockwork::class ],
					$pimple[ Request::class ]
				)
			);
		}
	}

	public function register( Plugin $plugin ): void {
		$pimple = $plugin->get_pimple();

		$pimple[ Clockwork_Support::class ] = static function ( Container $pimple ) {
			return new Clockwork_Support(
				$pimple[ Clockwork::class ],
				$pimple[ Data_Source_Factory::class ],
				$pimple[ Read_Only_Configuration::class ]
			);
		};

		$pimple[ Clockwork::class ] = static function ( Container $pimple ) {
			return ( new Clockwork() )
				->authenticator( $pimple[ AuthenticatorInterface::class ] )
				->request( $pimple[ Request::class ] )
				->storage( $pimple[ StorageInterface::class ] );
		};

		$pimple[ Authenticator_Factory::class ] = static function () {
			return new Authenticator_Factory();
		};

		$pimple[ AuthenticatorInterface::class ] = static function ( Container $pimple ) {
			$config = $pimple[ Read_Only_Configuration::class ]->get( 'authentication' );
			$factory = $pimple[ Authenticator_Factory::class ];

			if ( ! ( $config['enabled'] ?? false ) ) {
				return $factory->create( 'null' );
			}

			$driver = $config['driver'] ?? 'simple';

			return $factory->create( $driver, $config['drivers'][ $driver ] ?? [] );
		};

		$pimple[ Storage_Factory::class ] = static function () {
			return new Storage_Factory();
		};

		$pimple[ StorageInterface::class ] = $pimple->factory( static function ( Container $pimple ) {
			$storage_config = $pimple[ Read_Only_Configuration::class ]->get( 'storage' );
			$driver = $storage_config['driver'];
			$driver_config = $storage_config['drivers'][ $driver ] ?? [];

			if (
				null === ( $driver_config['expiration'] ?? null )
				&& null !== $storage_config['expiration']
			) {
				$driver_config['expiration'] = $storage_config['expiration'];
			}

			return $pimple[ Storage_Factory::class ]->create( $driver, $driver_config );
		} );

		$pimple[ Log::class ] = static function () {
			return new Log();
		};

		$pimple[ Request::class ] = static function () {
			return new Request();
		};

		// Create request so we have id and start time available immediately.
		// Could probably even create it within Plugin::__construct() and save it to container.
		$pimple[ Request::class ];

		$pimple[ IncomingRequest::class ] = $pimple->factory( static function ( Container $pimple ) {
			return $pimple[ Incoming_Request::class ];
		} );

		$pimple[ Incoming_Request::class ] = static function () {
			return Incoming_Request::from_globals();
		};
	}

	public function registered( Plugin $plugin ): void {
		$pimple = $plugin->get_pimple();
		$config = $pimple[ Read_Only_Configuration::class ];

		$this->configure_serializer( $config );
		$this->configure_should_collect( $pimple[ Clockwork::class ]->shouldCollect(), $config );

		if ( $config->get( 'register_helpers', true ) ) {
			require_once __DIR__ . '/clock.php';
		}
	}

	private function configure_serializer( Read_Only_Configuration $config ): void {
		Serializer::defaults(
			[
				'limit' => $config->get( 'serialization.depth', 10 ),
				'blackbox' => $config->get(
					'serialization.blackbox',
					[
						\Pimple\Container::class,
						\Pimple\Psr11\Container::class,
					]
				),
				'traces' => $config->get( 'stack_traces.enabled', true ),
				'tracesSkip' => StackFilter::make()
					->isNotVendor(
						\array_merge(
							$config->get( 'stack_traces.skip_vendors', [] ),
							[ 'itsgoingd' ]
						)
					)
					->isNotNamespace( $config->get( 'stack_traces.skip_namespaces', [] ) )
					->isNotFunction( [ 'call_user_func', 'call_user_func_array' ] )
					->isNotClass( $config->get( 'stack_traces.skip_classes', [] ) ),
				'tracesLimit' => $config->get( 'stack_traces.limit', 10 ),
			]
		);
	}

	private function configure_should_collect( ShouldCollect $should_collect, Read_Only_Configuration $config ): void {
		$should_collect->merge(
			[
				'onDemand' => $config->get( 'requests.on_demand', false ),
				'sample' => $config->get( 'requests.sample', false ),
				'except' => $config->get( 'requests.except', [] ),
				'only' => $config->get( 'requests.only', [] ),
				'exceptPreflight' => $config->get( 'requests.except_preflight', true ),
			]
		);

		$should_collect->except( [ '/__clockwork(?:/.*)?' ] );
	}
}
