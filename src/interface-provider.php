<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork_For_Wp\Event_Management\Event_Manager;

interface Provider {
	public function boot( Event_Manager $events );

	public function register();

	public function registered();
}
