<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork_For_Wp\Event_Management\Subscriber;

interface Provides_Subscriber {
	public function create_subscriber(): Subscriber;
}
