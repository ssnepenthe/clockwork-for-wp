<?php

namespace Clockwork_For_Wp\Wp_Cli;

use Clockwork_For_Wp\Event_Management\Event_Manager;
use ReflectionProperty;
use WP_CLI;
use WP_CLI\Loggers\Execution;

class Cli_Collection_Helper {
	protected $events;
	protected $logger;

	public function __construct( Event_Manager $events ) {
		$this->events = $events;
	}

	public function initialize_logger() {
		// Replace the logger immediately.
		$this->replace_wp_cli_logger();

		// And flush on the latest non-shutdown hook.
		$this->events->on( 'wp_loaded', function() {
			$this->get_logger()->ob_end();
		}, Event_Manager::LATE_EVENT );
	}

	public function get_logger() {
		if ( null === $this->logger ) {
			$this->logger = new Execution( WP_CLI::get_runner()->in_color() );
			$this->logger->ob_start();
		}

		return $this->logger;
	}

	public function replace_wp_cli_logger() {
		// Method doesn't exist before https://github.com/wp-cli/wp-cli/commit/f854c9678604a405a38e26948344e0a5e75ec1c7.
		if ( \method_exists( WP_CLI::class, 'get_logger' ) ) {
			$current_logger = WP_CLI::get_logger();
		} else {
			$ref = new ReflectionProperty( WP_CLI::class, 'logger' );
			$ref->setAccessible( true );

			$current_logger = $ref->getValue();
		}

		// @todo ???
		if ( $current_logger instanceof Execution || $current_logger instanceof Logger_Chain ) {
			return;
		}

		$new_logger = new Logger_Chain( $current_logger, $this->get_logger() );

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
