<?php

namespace Clockwork_For_Wp;

use Closure;
use Daedalus\Plugin\EventDispatcher;
use Daedalus\Plugin\PluginEvent;
use Daedalus\Plugin\PluginInterface;
use Invoker\InvokerInterface;
use ToyWpEventManagement\EventManagerInterface;

class Event_Dispatcher extends EventDispatcher {
    private $invoker;

    public function __construct(
        EventManagerInterface $eventManager,
        PluginInterface $plugin,
        InvokerInterface $invoker
    ) {
        parent::__construct( $eventManager, $plugin );

        $this->invoker = $invoker;
    }

    protected function wrapCallback( $callback ): Closure {
        return function ( ...$args ) use ( $callback ) {
            if (
                isset( $args[0] )
                && $args[0] instanceof PluginEvent
                && $this->plugin !== $args[0]->getPlugin()
            ) {
                return;
            }

            return $this->invoker->call( $callback, $args );
        };
    }
};
