<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork_For_Wp\Cli_Data_Collection\Cli_Collection_Helper;

final class Is {
	private $clockwork;

	private $config;

	private $request;

	public function __construct( Read_Only_Configuration $config, Clockwork $clockwork, Request $request ) {
		$this->config = $config;
		$this->clockwork = $clockwork;
		$this->request = $request;
	}

	public function collecting_client_metrics(): bool {
		return (bool) $this->config->get( 'collect_client_metrics', true );
	}

	public function collecting_commands(): bool {
		return ( $this->enabled() || $this->config->get( 'collect_data_always', false ) )
			&& $this->running_in_console()
			&& $this->config->get( 'wp_cli.collect', false );
	}

	public function collecting_data() {
		return $this->collecting_commands() || $this->collecting_requests();
	}

	public function collecting_heartbeat_requests() {
		return (bool) $this->config->get( 'collect_heartbeat', true );
	}

	public function collecting_requests() {
		return ( $this->enabled() || $this->config->get( 'collect_data_always', false ) )
			&& ! $this->running_in_console()
			&& $this->clockwork->shouldCollect()->filter( $this->request->get_incoming_request() )
			&& ( ! $this->request->is_heartbeat() || $this->collecting_heartbeat_requests() );
	}

	public function command_filtered( $command ) {
		if ( 'clockwork' === \mb_substr( $command, 0, 9 ) ) {
			return true;
		}

		$only = $this->config->get( 'wp_cli.only', [] );

		if ( \count( $only ) > 0 ) {
			return ! \in_array( $command, $only, true );
		}

		$except = $this->config->get( 'wp_cli.except', [] );

		if ( $this->config->get( 'wp_cli.except_built_in_commands', true ) ) {
			$except = \array_merge( $except, Cli_Collection_Helper::get_core_command_list() );
		}

		return \in_array( $command, $except, true );
	}

	public function enabled() {
		return (bool) $this->config->get( 'enable', true );
	}

	public function feature_available( $feature ) {
		// @todo Allow custom conditions to be registered.
		if ( 'wpdb' === $feature ) {
			return \defined( 'SAVEQUERIES' ) && SAVEQUERIES;
		}
		if ( 'xdebug' === $feature ) {
			return \extension_loaded( 'xdebug' ); // @todo get_loaded_extensions()?
		}

		return true;
	}

	public function feature_enabled( $feature ) {
		return $this->config->get( "data_sources.{$feature}.enabled", false ) && $this->feature_available( $feature );
	}

	public function recording() {
		return $this->enabled() || $this->config->get( 'collect_data_always', false );
	}

	public function running_in_console() {
		// @todo Do we actually care if it is in console but not WP-CLI?
		return ( \defined( 'WP_CLI' ) && WP_CLI ) || \in_array( \PHP_SAPI, [ 'cli', 'phpdbg' ], true );
	}

	public function toolbar_enabled() {
		return (bool) $this->config->get( 'toolbar', true );
	}

	public function web_enabled() {
		return $this->config->get( 'enable', true ) && $this->config->get( 'web', true );
	}

	public function web_installed() {
		// @todo Don't use wp functions?
		if ( ! \function_exists( 'get_home_path' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		return \file_exists( \get_home_path() . '__clockwork/index.html' );
	}
}
