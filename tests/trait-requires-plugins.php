<?php

namespace Clockwork_For_Wp\Tests;

use function Clockwork_For_Wp\Tests\activate_plugins;
use function Clockwork_For_Wp\Tests\deactivate_plugins;

// @todo Ensure all plugins are deactivated beforeClass, activate plugins before,
//       deactivate plugins after, deactivate all plugins afterClass
trait Requires_Plugins {
	protected static function required_plugins() : array {
		return [];
	}

	/** @beforeClass */
	public static function activate_required_plugins() : void {
		if ( count( $plugins = static::required_plugins() ) > 0 ) {
			activate_plugins( ...$plugins );
		}
	}

	/** @afterClass */
	public static function deactivate_required_plugins() : void {
		if ( count( $plugins = static::required_plugins() ) > 0 ) {
			deactivate_plugins( ...$plugins );
		}
	}
}
