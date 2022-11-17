<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork_For_Wp\Api\Api_Module;
use Clockwork_For_Wp\Cli_Data_Collection\Cli_Data_Collection_Module;
use Clockwork_For_Wp\Data_Source\Data_Source_Module;
use Clockwork_For_Wp\Data_Source\Errors;
use Clockwork_For_Wp\Routing\Routing_Module;
use Clockwork_For_Wp\Web_App\Web_App_Module;
use Clockwork_For_Wp\Wp_Cli\Wp_Cli_Module;
use Daedalus\Pimple\PimpleConfigurator;
use Daedalus\Plugin\ContainerConfiguratorInterface;
use Daedalus\Plugin\Plugin as DaedalusPlugin;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use ToyWpEventManagement\EventDispatcherInterface;

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
		$this->setTopLevelCommandName( 'clockwork' );
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
}
