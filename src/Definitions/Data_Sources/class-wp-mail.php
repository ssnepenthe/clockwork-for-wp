<?php

namespace Clockwork_For_Wp\Definitions\Data_Sources;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition;
use Clockwork_For_Wp\Data_Sources\Wp_Mail as Wp_Mail_Data_Source;

class Wp_Mail extends Definition {
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
