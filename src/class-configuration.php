<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use League\Config\ConfigurationBuilderInterface;
use League\Config\ConfigurationInterface;

final class Configuration {
	/**
	 * @var ConfigurationBuilderInterface&ConfigurationInterface
	 */
	private $config;

	/**
	 * @param ConfigurationBuilderInterface&ConfigurationInterface $config
	 */
	public function __construct( $config ) {
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

	public function merge( array $config = [] ): void {
		$this->config->merge( $config );
	}

	public function reader(): Read_Only_Configuration {
		return new Read_Only_Configuration( $this->config );
	}

	public function set( string $key, $value ): void {
		$this->config->set( $key, $value );
	}
}
