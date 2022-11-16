<?php

namespace Clockwork_For_Wp\Routing;

use Daedalus\Plugin\ModuleInterface;
use Daedalus\Plugin\PluginInterface;
use Daedalus\Routing\Events\RoutingModuleInitializing;
use Invoker\InvokerInterface;
use ToyWpRouting\InvokerBackedInvocationStrategy;

class Routing_Module implements ModuleInterface {
    public function register( PluginInterface $plugin ): void {
        $plugin->getEventDispatcher()->addListener(
            RoutingModuleInitializing::class,
            [ $this, 'onRoutingModuleInitializing' ]
        );
    }

    public function onRoutingModuleInitializing( RoutingModuleInitializing $event ): void {
        $invoker = $event->assertPluginIsAvailable()
            ->getPlugin()
            ->getContainer()
            ->get( InvokerInterface::class );

        $event->setInvocationStrategy( new InvokerBackedInvocationStrategy( $invoker ) );
    }
}
