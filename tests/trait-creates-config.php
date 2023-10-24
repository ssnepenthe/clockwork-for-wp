<?php

namespace Clockwork_For_Wp\Tests;

use Clockwork_For_Wp\Configuration;
use Clockwork_For_Wp\Read_Only_Configuration;
use League\Config\Configuration as LeagueConfiguration;

trait Creates_Config {
	public function create_config( array $user_config = [] ): Read_Only_Configuration {
		$schema = include dirname( __DIR__ ) . '/config/schema.php';

		$config = new Configuration( new LeagueConfiguration( $schema ) );

		$config->merge( $user_config );

		return $config->reader();
	}
}
