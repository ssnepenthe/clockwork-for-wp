<?php

namespace Clockwork_For_Wp\Definitions;

use Clockwork_For_Wp\Plugin;

// @todo Interface?
abstract class Definition {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function get_type() {
		return 'default';
	}

	abstract public function get_identifier();
	abstract public function get_subscribed_events();
	abstract public function get_value();
	abstract public function is_enabled();
}
