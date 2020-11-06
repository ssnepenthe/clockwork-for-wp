<?php

namespace Clockwork_For_Wp\Tests;

trait Manages_Plugins {
	protected static function activate_plugins( string ...$plugins ) : void {
		Cli::wp( 'plugin', 'activate', ...$plugins )->mustRun();
	}

	protected static function deactivate_plugins( string ...$plugins ) : void {
		Cli::wp( 'plugin', 'deactivate', ...$plugins )->mustRun();
	}

	protected static function required_plugins() : array {
		return [];
	}

	/** @beforeClass */
	public static function activate_required_plugins() : void {
		if ( count( $plugins = static::required_plugins() ) > 0 ) {
			static::activate_plugins( ...$plugins );
		}
	}

	/** @afterClass */
	public static function deactivate_required_plugins() : void {
		if ( count( $plugins = static::required_plugins() ) > 0 ) {
			static::deactivate_plugins( ...$plugins );
		}
	}
}
