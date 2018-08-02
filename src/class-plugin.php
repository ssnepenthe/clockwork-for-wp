<?php

namespace Clockwork_For_Wp;

use Closure;
use Pimple\Container;
use Pimple\ServiceProviderInterface as Provider;

class Plugin extends Container {
	const EARLY_EVENT = -999;
	const LATE_EVENT = 999;

	/**
	 * @var boolean
	 */
	protected $booted = false;

	/**
	 * @var array<int, Provider>
	 */
	protected $providers = [];

	/**
	 * @return void
	 */
	public function boot() {
		if ( $this->booted ) {
			return;
		}

		foreach ( $this->providers as $provider ) {
			if ( $provider instanceof Bootable_Provider ) {
				$provider->boot( $this );
			}
		}

		$this->booted = true;
	}

	/**
	 * @param  Provider            $provider
	 * @param  array<mixed, mixed> $values
	 * @return static
	 */
	public function register( Provider $provider, array $values = [] ) {
		$this->providers[] = $provider;

		parent::register( $provider, $values );

		return $this;
	}

	/**
	 * @param  string             $tag
	 * @param  array<int, string> $handler
	 * @param  integer            $priority
	 * @param  integer            $accepted_args
	 * @return static
	 */
	public function on( $tag, array $handler, $priority = 10, $accepted_args = 1 ) {
		// @todo Some validation on $handler?

		// Because add_action is just an alias of add_filter.
		add_filter(
			$tag,
			/**
			 * @return mixed
			 */
			function() use ( $handler ) {
				return call_user_func_array(
					[ $this[ $handler[0] ], $handler[1] ],
					func_get_args()
				);
			},
			$priority,
			$accepted_args
		);

		return $this;
	}
}
