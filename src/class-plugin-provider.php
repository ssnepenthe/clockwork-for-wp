<?php

namespace Clockwork_For_Wp;

use Clockwork_For_Wp\Event_Management\Event_Manager;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Pimple\Psr11\Container;

class Plugin_Provider extends Base_Provider {
	public function register() {
		// @todo Move to Plugin constructor?
		$this->plugin[ Plugin::class ] = $this->plugin;

		$this->plugin[ Config::class ] = function() {
			$values = include __DIR__ . '/config.php';
			$config = new Config( $values );

			$this->plugin[ Event_Manager::class ]->trigger( 'cfw_config_init', $config );

			return $config;
		};

		$this->plugin[ Invoker::class ] = function() {
			$psr_container = new Container( $this->plugin->get_container() );

			return new Invoker(
				new ResolverChain( [
					new TypeHintContainerResolver( $psr_container ),
					new ParameterNameContainerResolver( $psr_container ),
					new NumericArrayResolver(),
					new AssociativeArrayResolver(),
					new DefaultValueResolver(),
				] ),
				$psr_container
			);
		};
	}
}
