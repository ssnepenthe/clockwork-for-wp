<?php

namespace Clockwork_For_Wp\Event_Management;

interface Subscriber {
	public function subscribe_to_events( Event_Manager $event_manager ) : void;
}
