<?php

namespace Clockwork_For_Wp\Wp_Cli;

use ReflectionProperty;
use WP_CLI;

class Cli_Collection_Helper {
	protected static $logger;
	protected static $logger_initialized = false;

	public static function initialize_logger() {
		if ( static::$logger_initialized ) {
			return;
		}

		WP_CLI::set_logger(
			new Logger_Chain( static::get_wp_cli_logger(), static::get_plugin_logger() )
		);

		static::$logger_initialized = true;
	}

	public static function get_plugin_logger() {
		if ( null === static::$logger ) {
			static::$logger = new Recorder( WP_CLI::get_runner()->in_color() );
			static::$logger->ob_start();
		}

		return static::$logger;
	}

	public static function get_wp_cli_logger() {
		// Method doesn't exist before https://github.com/wp-cli/wp-cli/commit/f854c9678604a405a38e26948344e0a5e75ec1c7.
		if ( \method_exists( WP_CLI::class, 'get_logger' ) ) {
			$logger = WP_CLI::get_logger();
		} else {
			$ref = new ReflectionProperty( WP_CLI::class, 'logger' );
			$ref->setAccessible( true );

			$logger = $ref->getValue();
		}

		return $logger;
	}

	public static function get_core_command_list() {
		$commands = include __DIR__ . '/core-command-list.php';

		return $commands;
	}

	public static function get_clockwork_command_list() {
		$commands = include __DIR__ . '/clockwork-command-list.php';

		return $commands;
	}
}
