<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

interface Provider {
	public function boot( Plugin $plugin );

	public function register( Plugin $plugin );

	public function registered();
}
