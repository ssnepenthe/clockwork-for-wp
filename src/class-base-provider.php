<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork_For_Wp\Event_Management\Event_Manager;

/**
 * @internal
 */
abstract class Base_Provider implements Provider {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function boot( Event_Manager $events ): void {
	}

	public function register(): void {
	}

	public function registered(): void {
	}

	protected function subscribers(): array {
		return [];
	}
}
