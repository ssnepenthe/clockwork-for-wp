<?php

namespace Clockwork_For_Wp\Event_Management;

interface Managed_Subscriber {
	public function get_subscribed_events() : array;
}
