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
use Daedalus\Pimple\Events\AddingContainerDefinitions;
use Daedalus\Plugin\Events\ManagingSubscribers;
use Daedalus\Plugin\Events\PluginBooting;
use Daedalus\Plugin\Events\PluginLocking;
use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use League\Config\ConfigurationInterface;
use Psr\Container\ContainerInterface;

use function Daedalus\Pimple\factory;

/**
 * @internal
 */
final class Clockwork_Module implements ModuleInterface {
	public function onPluginBooting( PluginBooting $event ): void {
		// Clockwork instance is resolved even when we are not collecting data in order to take
		// advantage of helper methods like shouldCollect.
		// This ensures data sources are only registered on plugins_loaded when enabled.
		$support = $event->assertPluginIsAvailable()
			->getPlugin()
			->getContainer()
			->get( Clockwork_Support::class );

		if ( $support->is_collecting_data() ) {
			$support->add_data_sources();
		}
	}

	public function onManagingSubscribers( ManagingSubscribers $event ): void {
		$support = $event->assertPluginIsAvailable()
			->getPlugin()
			->getContainer()
			->get( Clockwork_Support::class );

		if ( $support->is_collecting_data() ) {
			$event->addSubscriber( Clockwork_Subscriber::class );
		}
	}

	public function onAddingContainerDefinitions( AddingContainerDefinitions $event ): void {
		$event->addDefinitions( [
			Clockwork_Subscriber::class => static function ( ContainerInterface $container ) {
				return new Clockwork_Subscriber( $container->get( Clockwork_Support::class ) );
			},
			Clockwork_Support::class => static function ( ContainerInterface $container ) {
				return new Clockwork_Support(
					$container->get( Clockwork::class ),
					$container->get( Data_Source_Factory::class ),
					$container->get( ConfigurationInterface::class ),
					$container->get( Incoming_Request::class )
				);
			},
			Clockwork::class => static function ( ContainerInterface $container ) {
				return ( new Clockwork() )
					->authenticator( $container->get( AuthenticatorInterface::class ) )
					->request( $container->get( Request::class ) )
					->storage( $container->get( StorageInterface::class ) );
			},
			Authenticator_Factory::class => static function () {
				return new Authenticator_Factory();
			},
			AuthenticatorInterface::class => static function ( ContainerInterface $container ) {
				$config = $container->get( ConfigurationInterface::class )->get( 'authentication' );
				$factory = $container->get( Authenticator_Factory::class );

				if ( ! ( $config['enabled'] ?? false ) ) {
					return $factory->create( 'null' );
				}

				$driver = $config['driver'] ?? 'simple';

				return $factory->create( $driver, $config['drivers'][ $driver ] ?? [] );
			},
			Storage_Factory::class => static function () {
				return new Storage_Factory();
			},
			StorageInterface::class => factory( static function ( ContainerInterface $container ) {
				$storage_config = $container->get( ConfigurationInterface::class )->get( 'storage' );
				$driver = $storage_config['driver'];
				$driver_config = $storage_config['drivers'][ $driver ] ?? [];

				if (
					null === ( $driver_config['expiration'] ?? null )
					&& null !== $storage_config['expiration']
				) {
					$driver_config['expiration'] = $storage_config['expiration'];
				}

				return $container->get( Storage_Factory::class )->create( $driver, $driver_config );
			} ),
			Log::class => static function () {
				return new Log();
			},
			Request::class => static function () {
				// @todo move out to plugin class with Errors::register()
				return new Request();
			},
			IncomingRequest::class => factory( static function ( ContainerInterface $container ) {
				return $container->get( Incoming_Request::class );
			} ),
			Incoming_Request::class => static function () {
				return Incoming_Request::from_globals();
			},
		]);

		// @todo !!!!!!!!!!!!!
		// Create request so we have id and start time available immediately.
		// Could probably even create it within Plugin::__construct() and save it to container.
		// Request::class;
	}

	public function onPluginLocking( PluginLocking $event ): void {
		$container = $event->assertPluginIsAvailable()->getPlugin()->getContainer();
		$support = $container->get( Clockwork_Support::class );
		$should_collect = $container->get( Clockwork::class )->shouldCollect();

		$this->configure_serializer( $support );
		$this->configure_should_collect( $should_collect, $support );

		if ( $support->config( 'register_helpers', true ) ) {
			require_once __DIR__ . '/clock.php';
		}
	}

	public function register( PluginInterface $plugin ): void {
		$eventDispatcher = $plugin->getEventDispatcher();

		// @todo conditional plugin booting and managing subscribers?
		$eventDispatcher->addListener(
			AddingContainerDefinitions::class,
			[ $this, 'onAddingContainerDefinitions' ]
		);
		$eventDispatcher->addListener(
			ManagingSubscribers::class,
			[ $this, 'onManagingSubscribers' ]
		);
		$eventDispatcher->addListener( PluginBooting::class, [ $this, 'onPluginBooting' ] );
		$eventDispatcher->addListener( PluginLocking::class, [ $this, 'onPluginLocking' ] );
	}

	private function configure_serializer( Clockwork_Support $support ): void {
		Serializer::defaults(
			[
				'limit' => $support->config( 'serialization.depth', 10 ),
				'blackbox' => $support->config(
					'serialization.blackbox',
					[
						\Pimple\Container::class,
						\Pimple\Psr11\Container::class,
					]
				),
				'traces' => $support->config( 'stack_traces.enabled', true ),
				'tracesSkip' => StackFilter::make()
					->isNotVendor(
						\array_merge(
							$support->config( 'stack_traces.skip_vendors', [] ),
							[ 'itsgoingd' ]
						)
					)
					->isNotNamespace( $support->config( 'stack_traces.skip_namespaces', [] ) )
					->isNotFunction( [ 'call_user_func', 'call_user_func_array' ] )
					->isNotClass( $support->config( 'stack_traces.skip_classes', [] ) ),
				'tracesLimit' => $support->config( 'stack_traces.limit', 10 ),
			]
		);
	}

	private function configure_should_collect( ShouldCollect $should_collect, Clockwork_Support $support ): void {
		$should_collect->merge(
			[
				'onDemand' => $support->config( 'requests.on_demand', false ),
				'sample' => $support->config( 'requests.sample', false ),
				'except' => $support->config( 'requests.except', [] ),
				'only' => $support->config( 'requests.only', [] ),
				'exceptPreflight' => $support->config( 'requests.except_preflight', true ),
			]
		);

		$should_collect->except( [ '/__clockwork(?:/.*)?' ] );
	}
}
