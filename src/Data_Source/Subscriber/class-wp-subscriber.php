<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Wp;
use Clockwork_For_Wp\Event_Management\Subscriber;

class Wp_Subscriber implements Subscriber {
	protected Wp $data_source;

	public function __construct( Wp $data_source ) {
		$this->data_source = $data_source;
	}

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => 'on_cfw_pre_resolve',
		];
	}

	public function on_cfw_pre_resolve( \WP $wp ): void {
		// @todo Move to rewrite?
		foreach ( [ 'request', 'query_string', 'matched_rule', 'matched_query' ] as $var ) {
			if ( \property_exists( $wp, $var ) && $wp->{$var} ) {
				$this->data_source->add_variable( $var, $wp->{$var} );
			}
		}
	}
}
