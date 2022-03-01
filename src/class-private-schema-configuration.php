<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use League\Config\ConfigurationInterface;
use League\Config\MutableConfigurationInterface;

/**
 * @internal
 */
final class Private_Schema_Configuration implements ConfigurationInterface, MutableConfigurationInterface {
	private $config;

	public function __construct( $config ) {
		$this->config = $config;
	}

	public function exists( string $key ): bool {
		return $this->config->exists( $key );
	}

	public function get( string $key ) {
		return $this->config->get( $key );
	}

	public function merge( array $config = [] ): void {
		$this->config->merge( $config );
	}

	public function set( string $key, $value ): void {
		$this->config->set( $key, $value );
	}
}
