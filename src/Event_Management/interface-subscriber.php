<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Event_Management;

interface Subscriber {
	public function get_subscribed_events(): array;
}
