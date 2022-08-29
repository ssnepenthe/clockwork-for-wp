<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use League\Config\Configuration;
use League\Config\ConfigurationBuilderInterface;
use League\Config\ConfigurationInterface;
use Pimple\Container;

/**
 * @internal
 */
final class Plugin_Provider extends Base_Provider {
	public function boot(): void {
		if (
			$this->plugin->is_enabled()
			|| $this->plugin->is_web_enabled()
			|| $this->plugin->is_web_installed()
		) {
			parent::boot();
		}
	}

	public function register(): void {
		require_once __DIR__ . '/plugin-helpers.php';

		$pimple = $this->plugin->get_pimple();

		$pimple[ ConfigurationBuilderInterface::class ] = static function ( Container $pimple ) {
			$schema = include \dirname( __DIR__ ) . '/config/schema.php';
			$defaults = include \dirname( __DIR__ ) . '/config/defaults.php';

			$config = new Configuration( $schema );

			$config->merge( $defaults );

			$pimple[ Event_Manager::class ]->trigger(
				'cfw_config_init',
				new Private_Schema_Configuration( $config )
			);

			return $config;
		};

		$pimple[ ConfigurationInterface::class ] = static function ( Container $pimple ) {
			return $pimple[ ConfigurationBuilderInterface::class ]->reader();
		};

		$pimple[ Invoker::class ] = function () {
			$psr_container = $this->plugin->get_container();

			return new Invoker(
				new ResolverChain(
					[
						new TypeHintContainerResolver( $psr_container ),
						new ParameterNameContainerResolver( $psr_container ),
						new NumericArrayResolver(),
						new AssociativeArrayResolver(),
						new DefaultValueResolver(),
					]
				),
				$psr_container
			);
		};

		$pimple[ Metadata::class ] = static function ( Container $pimple ) {
			return new Metadata(
				$pimple[ Clockwork_Support::class ],
				$pimple[ Clockwork::class ]->storage()
			);
		};

		$pimple[ Plugin_Subscriber::class ] = static function () {
			return new Plugin_Subscriber();
		};
	}

	protected function subscribers(): array {
		return [ Plugin_Subscriber::class ];
	}
}
