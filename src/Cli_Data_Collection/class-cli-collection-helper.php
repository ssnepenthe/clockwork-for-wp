<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Cli_Data_Collection;

use ReflectionProperty;
use WP_CLI;

/**
 * @internal
 */
final class Cli_Collection_Helper {
	private static $logger;

	private static $logger_initialized = false;

	public static function get_core_command_list() {
		$commands = include self::get_core_command_list_path();

		return $commands;
	}

	public static function get_core_command_list_path(): string {
		$dir = \dirname( \_cfw_instance()->getFile() );

		return "{$dir}/generated/wp-cli-core-command-list.php";
	}

	public static function get_plugin_logger() {
		if ( null === self::$logger ) {
			self::$logger = new Recorder( WP_CLI::get_runner()->in_color() );
			self::$logger->ob_start();
		}

		return self::$logger;
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

	public static function initialize_logger(): void {
		if ( self::$logger_initialized ) {
			return;
		}

		WP_CLI::set_logger(
			new Logger_Chain( self::get_wp_cli_logger(), self::get_plugin_logger() )
		);

		self::$logger_initialized = true;
	}
}
