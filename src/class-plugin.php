<?php

namespace Clockwork_For_Wp;

use Pimple\Container;

class Plugin {
	const EARLY_EVENT = -999;
	const LATE_EVENT = 999;

	protected $booted = false;
	protected $container;
	protected $definitions = [];
	protected $initialized = false;

	public function __construct( array $values = [] ) {
		$this->container = new Container( $values );
		$this->container->register( new WordPress_Provider() );
	}

	public function boot() {
		if ( $this->booted ) {
			return;
		}

		foreach ( $this->definitions() as $definition ) {
			$this->attach( $definition );
		}

		$this->booted = true;
	}

	public function container( $identifier = null ) {
		return $this->container;
	}

	public function definitions() {
		if ( 0 === count( $this->definitions ) ) {
			$this->definitions = [
				new Definitions\Data_Sources\Cache( $this ),
				new Definitions\Data_Sources\Errors( $this ),
				new Definitions\Data_Sources\Php( $this ),
				new Definitions\Data_Sources\Rest_Api( $this ),
				new Definitions\Data_Sources\Theme( $this ),
				new Definitions\Data_Sources\WordPress( $this ),
				new Definitions\Data_Sources\Wp_Hook( $this ),
			];
		}

		return $this->definitions;
	}

	public function initialize() {
		if ( $this->initialized ) {
			return;
		}

		foreach ( $this->definitions() as $definition ) {
			$this->register( $definition );
		}

		$this->initialized = true;
	}

	public function service( $identifier ) {
		return $this->container[ $identifier ];
	}

	protected function attach( $definition ) {
		// @todo instanceof check?
		foreach ( $definition->get_subscribed_events() as $args ) {
			$this->on(
				$args[0],
				$definition->get_identifier(),
				$args[1],
				isset( $args[2] ) ? $args[2] : 10,
				isset( $args[3] ) ? $args[3] : 1
			);
		}
	}

	protected function on( $tag, $identifier, $method, $priority = 10, $accepted_args = 1 ) {
		// Because add_action is just an alias of add_filter.
		add_filter( $tag, function() use ( $identifier, $method ) {
			return call_user_func_array(
				[ $this->container[ $identifier ], $method ],
				func_get_args()
			);
		}, $priority, $accepted_args );
	}

	protected function register( $definition ) {
		switch ( $definition->get_type() ) {
			// @todo factory, raw, protect, etc.
			default:
				$this->container[ $definition->get_identifier() ] = $definition->get_value();
				break;
		}
	}
}
