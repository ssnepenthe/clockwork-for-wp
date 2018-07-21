<?php

namespace Clockwork_For_Wp;

use Closure;
use Pimple\Container;
use Pimple\ServiceProviderInterface as Provider;

class Plugin extends Container {
	const EARLY_EVENT = -999;
	const LATE_EVENT = 999;

	protected $booted = false;
	protected $providers = [];

	public function boot() {
		if ( $this->booted ) {
			return;
		}

		foreach ( $this->providers as $provider ) {
			if ( $provider instanceof Bootable_Provider ) {
				$provider->boot( $this );
			}

			// @todo
			// if ( $provider instanceof Subscriber_Provider ) {
			//     $provider->subscribe( $this );
			// }
		}

		$this->booted = true;
	}

	public function register( Provider $provider, array $values = [] )
	{
		$this->providers[] = $provider;

		parent::register( $provider, $values );

		// @todo
		// if ( $this->booted ) {
		// 	$provider->boot( $this );
		// }

		return $this;
	}

	public function on( string $tag, array $handler, int $priority = 10, int $accepted_args = 1 ) {
		// @todo Some validation on $handler?

		// Because add_action is just an alias of add_filter.
		add_filter( $tag, function() use ( $handler ) {
			return call_user_func_array( [ $this[ $handler[0] ], $handler[1] ], func_get_args() );
		}, $priority, $accepted_args );

		return $this;
	}
}
