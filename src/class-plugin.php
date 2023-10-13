<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Api\Api_Provider;
use Clockwork_For_Wp\Cli_Data_Collection\Cli_Collection_Helper;
use Clockwork_For_Wp\Cli_Data_Collection\Cli_Data_Collection_Provider;
use Clockwork_For_Wp\Data_Source\Data_Source_Provider;
use Clockwork_For_Wp\Data_Source\Errors;
use Clockwork_For_Wp\Event_Management\Event_Management_Provider;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Routing\Routing_Provider;
use Clockwork_For_Wp\Web_App\Web_App_Provider;
use Clockwork_For_Wp\Wp_Cli\Wp_Cli_Provider;
use InvalidArgumentException;
use League\Config\ConfigurationInterface;
use Pimple\Container;
use RuntimeException;

/**
 * @internal
 */
final class Plugin {
	private $booted = false;

	private $locked = false;

	private $pimple;

	private $providers = [];

	public function __construct( ?array $providers = null, ?array $values = null ) {
		if ( null === $providers ) {
			$providers = [
				Clockwork_Provider::class,
				Plugin_Provider::class,
				Wordpress_Provider::class,

				Api_Provider::class,
				Cli_Data_Collection_Provider::class,
				Data_Source_Provider::class,
				Event_Management_Provider::class,
				Routing_Provider::class,
				Web_App_Provider::class,
				Wp_Cli_Provider::class,
			];
		}

		$this->pimple = new Container( $values ?: [] );
		$this->pimple[ self::class ] = $this;

		foreach ( $providers as $provider ) {
			if ( \is_string( $provider ) && \is_subclass_of( $provider, Base_Provider::class ) ) {
				$provider = new $provider( $this );
			}

			if ( ! $provider instanceof Provider ) {
				throw new InvalidArgumentException( 'Invalid provider type in plugin constructor' );
			}

			$this->register( $provider );
		}
	}

	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		foreach ( $this->providers as $provider ) {
			$provider->boot();
		}

		$this->booted = true;
	}

	public function config( $path, $default = null ) {
		if ( ! isset( $this->pimple[ ConfigurationInterface::class ] ) ) {
			return $default;
		}

		$config = $this->pimple[ ConfigurationInterface::class ];

		if ( ! $config->exists( $path ) ) {
			return $default;
		}

		return $config->get( $path );
	}

	public function get_pimple(): Container {
		return $this->pimple;
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
		$clockwork = $this->pimple[ Clockwork::class ];
		$request = $this->pimple[ IncomingRequest::class ];

		return ( $this->is_enabled() || $this->config( 'collect_data_always', false ) )
			&& ! $this->is_running_in_console()
			&& $clockwork->shouldCollect()->filter( $request )
			&& ( ! $request->is_heartbeat() || $this->is_collecting_heartbeat_requests() );
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
		// @todo Don't use wp functions in plugin class?
		if ( ! \function_exists( 'get_home_path' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		return \file_exists( \get_home_path() . '__clockwork/index.html' );
	}

	// @todo Method name?
	public function lock(): void {
		if ( $this->locked ) {
			return;
		}

		foreach ( $this->providers as $provider ) {
			$provider->registered();
		}

		$this->locked = true;
	}

	public function register( Provider $provider ) {
		if ( $this->locked ) {
			throw new RuntimeException( 'Cannot register providers after plugin has been locked' );
		}

		$provider->register();

		$this->providers[ \get_class( $provider ) ] = $provider;

		return $this;
	}

	public function run(): void {
		// Resolve error handler immediately so we catch as many errors as possible.
		// @todo Move to plugin constructor?
		Errors::get_instance()->register();

		$this->pimple[ Event_Manager::class ]
			->on(
				'plugin_loaded',
				function ( $file ): void {
					if ( $this->pimple[ 'file' ] !== \realpath( $file ) ) {
						return;
					}

					$this->lock();
				},
				Event_Manager::EARLY_EVENT
			)
			->on( 'plugins_loaded', [ $this, 'boot' ], Event_Manager::EARLY_EVENT );
	}
}
