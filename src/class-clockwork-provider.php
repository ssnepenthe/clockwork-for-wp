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
use Clockwork\Storage\StorageInterface;
use Clockwork_For_Wp\Data_Source\Data_Source_Factory;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use League\Config\ConfigurationInterface;
use Pimple\Container;

/**
 * @internal
 */
final class Clockwork_Provider extends Base_Provider {
	public function boot( Plugin $plugin ): void {
		if ( $plugin->is_collecting_data() ) {
			$pimple = $plugin->get_pimple();

			// Clockwork instance is resolved even when we are not collecting data in order to take
			// advantage of helper methods like shouldCollect.
			// This ensures data sources are only registered on plugins_loaded when enabled.
			$pimple[ Clockwork_Support::class ]->add_data_sources();

			$pimple[ Event_Manager::class ]->attach(
				new Clockwork_Subscriber(
					$pimple[ Plugin::class ],
					$pimple[ Event_Manager::class ],
					$pimple[ Clockwork::class ],
					$pimple[ Request::class ]
				)
			);
		}
	}

	public function register(): void {
		$pimple = $this->plugin->get_pimple();

		$pimple[ Clockwork_Support::class ] = static function ( Container $pimple ) {
			return new Clockwork_Support(
				$pimple[ Clockwork::class ],
				$pimple[ Data_Source_Factory::class ],
				$pimple[ ConfigurationInterface::class ]
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
			$config = $pimple[ ConfigurationInterface::class ]->get( 'authentication' );
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
			$storage_config = $pimple[ ConfigurationInterface::class ]->get( 'storage' );
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

	public function registered(): void {
		$this->configure_serializer();
		$this->configure_should_collect();

		if ( $this->plugin->config( 'register_helpers', true ) ) {
			require_once __DIR__ . '/clock.php';
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
		$should_collect = $this->plugin->get_pimple()[ Clockwork::class ]->shouldCollect();

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
