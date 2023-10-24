<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

/**
 * @internal
 */
abstract class Base_Provider implements Provider {
	public function boot( Plugin $plugin ): void {
	}

	public function register( Plugin $plugin ): void {
	}

	public function registered( Plugin $plugin ): void {
	}
}
