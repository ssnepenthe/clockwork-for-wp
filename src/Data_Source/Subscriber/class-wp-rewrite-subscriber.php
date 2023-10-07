<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Wp_Rewrite;
use Clockwork_For_Wp\Event_Management\Subscriber;

class Wp_Rewrite_Subscriber implements Subscriber {
	protected Wp_Rewrite $data_source;

	public function __construct( Wp_Rewrite $data_source ) {
		$this->data_source = $data_source;
	}

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => 'on_cfw_pre_resolve',
		];
	}

	public function on_cfw_pre_resolve( \WP_Rewrite $wp_rewrite ): void {
		$this->data_source
			->set_structure( $wp_rewrite->permalink_structure )
			->set_trailing_slash( $wp_rewrite->use_trailing_slashes )
			->set_front( $wp_rewrite->front )
			->set_rules( $wp_rewrite->wp_rewrite_rules() );
	}
}
