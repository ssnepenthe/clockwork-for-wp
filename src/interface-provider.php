<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

interface Provider {
	public function boot();

	public function register();

	public function registered();
}
