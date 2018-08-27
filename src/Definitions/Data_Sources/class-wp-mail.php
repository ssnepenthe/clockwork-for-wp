<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Wp_Mail as Wp_Mail_Data_Source;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface as Toggling_Definition;
use Clockwork_For_Wp\Definitions\Subscribing_Definition_Interface as Subscribing_Definition;

class Wp_Mail extends Definition implements Subscribing_Definition, Toggling_Definition {
	public function get_identifier() {
		return 'data_sources.wp_mail';
	}

	public function get_subscribed_events() {
		return [
			[ 'wp_mail',        'on_wp_mail'        ],
			[ 'wp_mail_failed', 'on_wp_mail_failed' ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Wp_Mail_Data_Source();
		};
	}

	public function is_enabled() {
		return $this->plugin->is_data_source_enabled( 'wp_mail' );
	}
}
