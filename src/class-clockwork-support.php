<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork_For_Wp\Data_Source\Data_Source_Factory;
use League\Config\ConfigurationInterface;

/**
 * @internal
 */
final class Clockwork_Support {
	private $clockwork;

	private $config;

	private $factory;

	private $request;

	public function __construct(
		Clockwork $clockwork,
		Data_Source_Factory $factory,
		ConfigurationInterface $config,
		Incoming_Request $request
	) {
		$this->clockwork = $clockwork;
		$this->factory = $factory;
		$this->config = $config;
		$this->request = $request;
	}

	public function add_data_sources(): void {
		$sensitive_patterns = $this->config->get( 'requests.sensitive_patterns' );

		$this->clockwork->addDataSource(
			$this->factory->create( 'php', \compact( 'sensitive_patterns' ) )
		);

		foreach ( $this->get_enabled_data_sources() as $data_source ) {
			$this->clockwork->addDataSource( $data_source );
		}
	}

	public function get_enabled_data_sources(): array {
		$data_sources = [];

		foreach ( $this->config( 'data_sources', [] ) as $name => $data_source ) {
			if ( ( $data_source['enabled'] ?? false ) && $this->is_feature_available( $name ) ) {
				$data_sources[] = $this->factory->create( $name, $data_source['config'] ?? [] );
			}
		}

		return $data_sources;
	}

	public function extend_request( $data ) {
		$this->add_data_sources();

		return $this->clockwork->extendRequest( $data );
	}

	public function config( $path, $default = null ) {
		if ( ! $this->config->exists( $path ) ) {
			return $default;
		}

		return $this->config->get( $path );
	}

	public function is_collecting_client_metrics() {
		return (bool) $this->config( 'collect_client_metrics', true );
	}

	public function is_collecting_commands() {
		return ( $this->is_enabled() || $this->config( 'collect_data_always', false ) )
			&& $this->is_running_in_console()
			&& $this->config( 'wp_cli.collect', false );
	}

	public function is_collecting_data() {
		return $this->is_collecting_commands() || $this->is_collecting_requests();
	}

	public function is_collecting_heartbeat_requests() {
		return (bool) $this->config( 'collect_heartbeat', true );
	}

	public function is_collecting_requests() {
		return ( $this->is_enabled() || $this->config( 'collect_data_always', false ) )
			&& ! $this->is_running_in_console()
			&& $this->clockwork->shouldCollect()->filter( $this->request )
			&& ( ! $this->request->is_heartbeat() || $this->is_collecting_heartbeat_requests() );
	}

	public function is_command_filtered( $command ) {
		if ( 'clockwork' === \mb_substr( $command, 0, 9 ) ) {
			return true;
		}

		$only = $this->config( 'wp_cli.only', [] );

		if ( \count( $only ) > 0 ) {
			return ! \in_array( $command, $only, true );
		}

		$except = $this->config( 'wp_cli.except', [] );

		if ( $this->config( 'wp_cli.except_built_in_commands', true ) ) {
			$except = \array_merge( $except, Cli_Collection_Helper::get_core_command_list() );
		}

		return \in_array( $command, $except, true );
	}

	public function is_enabled() {
		return (bool) $this->config( 'enable', true );
	}

	public function is_feature_available( $feature ) {
		// @todo Allow custom conditions to be registered.
		if ( 'wpdb' === $feature ) {
			return \defined( 'SAVEQUERIES' ) && SAVEQUERIES;
		}
		if ( 'xdebug' === $feature ) {
			return \extension_loaded( 'xdebug' ); // @todo get_loaded_extensions()?
		}

		return true;
	}

	public function is_feature_enabled( $feature ) {
		return $this->config( "data_sources.{$feature}.enabled", false )
			&& $this->is_feature_available( $feature );
	}

	public function is_recording() {
		return $this->is_enabled() || $this->config( 'collect_data_always', false );
	}

	public function is_running_in_console() {
		// @todo Do we actually care if it is in console but not WP-CLI?
		return ( \defined( 'WP_CLI' ) && WP_CLI ) || \in_array( \PHP_SAPI, [ 'cli', 'phpdbg' ], true );
	}

	public function is_toolbar_enabled() {
		return (bool) $this->config( 'toolbar', true );
	}

	public function is_web_enabled() {
		return $this->config( 'enable', true ) && $this->config( 'web', true );
	}

	public function is_web_installed() {
		// @todo Don't use wp functions in support class?
		if ( ! \function_exists( 'get_home_path' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		return \file_exists( \get_home_path() . '__clockwork/index.html' );
	}
}
