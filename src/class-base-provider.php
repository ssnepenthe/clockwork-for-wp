<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

/**
 * @internal
 */
abstract class Base_Provider implements Provider {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function boot(): void {
		$subscribers = $this->subscribers();

		if ( \count( $subscribers ) > 0 ) {
			$container = $this->plugin->get_container();
			$events = $container->get( Event_Manager::class );

			foreach ( $subscribers as $subscriber ) {
				if ( ! $subscriber instanceof Subscriber ) {
					$subscriber = $container->get( $subscriber );
				}

				$events->attach( $subscriber );
			}
		}
	}

	public function register(): void {
	}

	public function registered(): void {
	}

	protected function subscribers(): array {
		return [];
	}
}
