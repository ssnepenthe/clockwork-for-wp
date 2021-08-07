<?php

namespace Clockwork_For_Wp;

interface Provider {
	public function boot();

	public function register();

	public function registered();
}
