<?php

namespace Clockwork_For_Wp;

use Clockwork_For_Wp\Event_Management\Event_Manager;

class Base_Provider implements Provider {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function boot() {
		if ( count( $subscribers = $this->subscribers() ) > 0 ) {
			$events = $this->plugin[ Event_Manager::class ];

			foreach ( $subscribers as $subscriber ) {
				$events->attach( $this->plugin[ $subscriber ] );
			}
		}
	}

	public function register() {

	}

	public function registered() {

	}

	protected function subscribers(): array {
		return [];
	}
}
