<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use InvalidArgumentException;

abstract class Base_Factory {
	protected bool $cache_enabled = false;

	protected array $custom_factories = [];

	protected array $instance_cache = [];

	public function create( string $name, array $config = [] ) {
		// Note to self: an ideal resusable/general purpose implementation would cache instances based on a combination
		// of $name and $config. I don't *think* this is strictly necessary in the case of this plugin, and I don't
		// really want to deal with the various closures that may be in $config. It's possible I may need to revisit
		// this at some point if/when this assumption proves to be incorrect.
		if ( $this->cache_enabled && \array_key_exists( $name, $this->instance_cache ) ) {
			return $this->instance_cache[ $name ];
		}

		if ( \array_key_exists( $name, $this->custom_factories ) ) {
			return $this->save_instance( $name, ( $this->custom_factories[ $name ] )( $config ) );
		}

		$method = "create_{$name}_instance";

		if ( \method_exists( $this, $method ) ) {
			return $this->save_instance( $name, ( [ $this, $method ] )( $config ) );
		}

		throw new InvalidArgumentException( "Unrecognized factory name: {$name}" );
	}

	public function register_custom_factory( string $name, callable $factory ) {
		$this->custom_factories[ $name ] = $factory;

		return $this;
	}

	protected function save_instance( string $name, $instance ) {
		if ( $this->cache_enabled ) {
			$this->instance_cache[ $name ] = $instance;
		}

		return $instance;
	}
}
