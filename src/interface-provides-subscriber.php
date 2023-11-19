<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use WpEventDispatcher\SubscriberInterface;

interface Provides_Subscriber {
	public function create_subscriber(): SubscriberInterface;
}
