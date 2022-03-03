<?php

namespace Clockwork_For_Wp\Tests;

use League\Config\Configuration;
use League\Config\ConfigurationInterface;

trait Creates_Config {
	public function create_config( array $user_config = [] ): ConfigurationInterface {
		$schema = include dirname( __DIR__ ) . '/config/schema.php';

		$config = new Configuration( $schema );

		$config->merge( $user_config );

		return $config->reader();
	}
}
