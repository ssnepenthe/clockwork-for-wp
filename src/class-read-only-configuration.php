<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use League\Config\ConfigurationInterface;

final class Read_Only_Configuration {
	private ConfigurationInterface $config;

	public function __construct( ConfigurationInterface $config ) {
		$this->config = $config;
	}

	public function exists( string $key ): bool {
		return $this->config->exists( $key );
	}

	public function get( string $key, $default = null ) {
		if ( ! $this->config->exists( $key ) ) {
			return $default;
		}

		return $this->config->get( $key );
	}
}
