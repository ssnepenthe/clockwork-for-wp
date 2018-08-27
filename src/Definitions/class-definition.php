<?php

namespace Clockwork_For_Wp\Definitions;

use Clockwork_For_Wp\Plugin;

abstract class Definition implements Definition_Interface {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	abstract public function get_identifier();
	abstract public function get_value();
}
