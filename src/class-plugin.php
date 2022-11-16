<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Api\Api_Module;
use Clockwork_For_Wp\Cli_Data_Collection\Cli_Collection_Helper;
use Clockwork_For_Wp\Cli_Data_Collection\Cli_Data_Collection_Module;
use Clockwork_For_Wp\Data_Source\Data_Source_Module;
use Clockwork_For_Wp\Data_Source\Errors;
use Clockwork_For_Wp\Routing\Routing_Module;
use Clockwork_For_Wp\Web_App\Web_App_Module;
use Clockwork_For_Wp\Wp_Cli\Wp_Cli_Module;
use Daedalus\Pimple\PimpleConfigurator;
use Daedalus\Plugin\ContainerConfiguratorInterface;
use Daedalus\Plugin\EventManagementModule;
use Daedalus\Plugin\Plugin as DaedalusPlugin;
use Daedalus\Plugin\PluginInitializationModule;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use League\Config\ConfigurationInterface;
use ToyWpEventManagement\EventDispatcherInterface;
use ToyWpEventManagement\Priority;

/**
 * @internal
 */
class Plugin extends DaedalusPlugin {
	protected ?InvokerInterface $invoker = null;

	protected function configure(): void {
		// @todo separate method from configure()? initialize()?
		Errors::get_instance()->register();

		$this->setName( 'Clockwork for WP' );
		$this->setFile( __DIR__ . '/../clockwork-for-wp.php' );
		$this->setPrefix( 'cfw_' );
		$this->setCacheDir( __DIR__ . '/../generated' );
		$this->enableModuleDiscovery();
	}

	protected function createModules(): array
	{
		// @todo provider -> module
		return [
			new Clockwork_Module(),
			new Plugin_Module(),
			new Api_Module(),
			new Cli_Data_Collection_Module(),
			new Data_Source_Module(),
			new Routing_Module(),
			new Web_App_Module(),
			new Wp_Cli_Module(),
		];
	}

	protected function createDefaultModules(): array {
		// @todo Probably not necessary? Default priorities should be fine...
		return [
			new PluginInitializationModule( Priority::EARLY, Priority::EARLY, Priority::EARLY ),
			new EventManagementModule(),
		];
	}

	protected function createContainerConfigurator(): ?ContainerConfiguratorInterface {
		return new PimpleConfigurator();
	}

	public function getInvoker(): InvokerInterface {
		if ( ! $this->invoker instanceof InvokerInterface ) {
			$this->invoker = new Invoker(
				new ResolverChain(
					[
						new TypeHintContainerResolver( $this->getContainer() ),
						new ParameterNameContainerResolver( $this->getContainer() ),
						new NumericArrayResolver(),
						new AssociativeArrayResolver(),
						new DefaultValueResolver(),
					]
				),
				$this->getContainer()
			);
		}

		return $this->invoker;
	}

	protected function createDefaultEventDispatcher(): EventDispatcherInterface {
		return new Event_Dispatcher( $this->getEventManager(), $this, $this->getInvoker() );
	}

	public function config( $path, $default = null ) {
		if ( ! $this->container->has( ConfigurationInterface::class ) ) {
			return $default;
		}

		$config = $this->container->get( ConfigurationInterface::class );

		if ( ! $config->exists( $path ) ) {
			return $default;
		}

		return $config->get( $path );
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
		$clockwork = $this->container->get( Clockwork::class );
		$request = $this->container->get( IncomingRequest::class );

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
}
