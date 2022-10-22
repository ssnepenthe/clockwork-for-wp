<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork_For_Wp\Plugin;
use Daedalus\Pimple\Events\AddingContainerDefinitions;
use Daedalus\Plugin\Events\ManagingSubscribers;
use Daedalus\Plugin\Events\PluginLocking;
use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use Psr\Container\ContainerInterface;
use ToyWpEventManagement\SubscriberInterface;

/**
 * @internal
 */
final class Data_Source_Module implements ModuleInterface {
	public function register( PluginInterface $plugin ): void {
		$eventDispatcher = $plugin->getEventDispatcher();

		$eventDispatcher->addListener( AddingContainerDefinitions::class, [ $this, 'onAddingContainerDefinitions' ] );
		$eventDispatcher->addListener( ManagingSubscribers::class, [ $this, 'onManagingSubscribers' ] );
		$eventDispatcher->addListener( PluginLocking::class, [ $this, 'onPluginLocking' ] );
	}

	public function onAddingContainerDefinitions( AddingContainerDefinitions $event ): void {
		$event->addDefinitions([
			Data_Source_Factory::class => static function ( ContainerInterface $container ) {
				return new Data_Source_Factory( $container->get( Plugin::class ) );
			},
		]);
	}

	public function onPluginLocking( PluginLocking $event ): void {
		// We have registered our error handler as early as possible in order to collect as many
		// errors as possible. However our config is not available that early so let's apply our
		// configuration now.
		$errors = Errors::get_instance();
		$plugin = $event->getPlugin();

		if ( $plugin->is_feature_enabled( 'errors' ) ) {
			$config = $plugin->config( 'data_sources.errors.config', [] );

			$except_types = $config['except_types'] ?? false;
			$only_types = $config['only_types'] ?? false;

			// Filter errors by type.
			$errors->addFilter( static function ( $error ) use ( $except_types, $only_types ) {
				if ( \is_int( $only_types ) ) {
					return ( $error['type'] & $only_types ) > 0;
				}

				if ( \is_int( $except_types ) ) {
					return ( $error['type'] & $except_types ) < 1;
				}

				return true;
			} );

			// Filter errors by message pattern.
			$errors->addFilter(
				( new Filter() )
					->except( $config['except_messages'] ?? [] )
					->only( $config['only_messages'] ?? [] )
					->to_closure( 'message' )
			);

			// Filter errors by file pattern.
			$errors->addFilter(
				( new Filter() )
					->except( $config['except_files'] ?? [] )
					->only( $config['only_files'] ?? [] )
					->to_closure( 'file' )
			);

			// Filter suppressed errors.
			$include_suppressed = $config['include_suppressed_errors'] ?? false;

			$errors->addFilter( static function ( $error ) use ( $include_suppressed ) {
				return ! $error['suppressed'] || $include_suppressed;
			} );

			$errors->reapply_filters();
		} else {
			$errors->unregister();
		}
	}

	public function onManagingSubscribers( ManagingSubscribers $event ): void {
		$data_source_factory = $event->getPlugin()->getContainer()->get( Data_Source_Factory::class );

		$event->addSubscribers(
			\array_filter(
				$data_source_factory->get_enabled_data_sources(),
				static function ( $data_source ) {
					return $data_source instanceof SubscriberInterface;
				}
			)
		);
	}
}
