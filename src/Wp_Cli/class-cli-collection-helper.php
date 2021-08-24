<?php

namespace Clockwork_For_Wp\Wp_Cli;

use Clockwork_For_Wp\Event_Management\Event_Manager;
use ReflectionProperty;
use WP_CLI;
use WP_CLI\Loggers\Execution;

class Cli_Collection_Helper {
	protected $logger;

	public function initialize_logger() {
		// Replace the logger immediately.
		$this->replace_wp_cli_logger();
	}

	public function get_logger() {
		if ( null === $this->logger ) {
			$this->logger = new Recorder( WP_CLI::get_runner()->in_color() );
			$this->logger->ob_start();
		}

		return $this->logger;
	}

	public function replace_wp_cli_logger() {
		// Method doesn't exist before https://github.com/wp-cli/wp-cli/commit/f854c9678604a405a38e26948344e0a5e75ec1c7.
		if ( \method_exists( WP_CLI::class, 'get_logger' ) ) {
			$previous_logger = WP_CLI::get_logger();
		} else {
			$ref = new ReflectionProperty( WP_CLI::class, 'logger' );
			$ref->setAccessible( true );

			$previous_logger = $ref->getValue();
		}

		if ( $previous_logger instanceof Logger_Chain ) {
			return;
		}

		$new_logger = new Logger_Chain( $previous_logger, $this->get_logger() );

		WP_CLI::set_logger( $new_logger );
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
