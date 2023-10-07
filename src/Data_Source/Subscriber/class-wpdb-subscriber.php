<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Wpdb;
use Clockwork_For_Wp\Event_Management\Subscriber;

use function Clockwork_For_Wp\prepare_wpdb_query;

class Wpdb_Subscriber implements Subscriber {
	protected Wpdb $data_source;

	public function __construct( Wpdb $data_source ) {
		$this->data_source = $data_source;
	}

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => 'on_cfw_pre_resolve',
		];
	}

	public function on_cfw_pre_resolve( \wpdb $wpdb ): void {
		if ( ! \is_array( $wpdb->queries ) || \count( $wpdb->queries ) < 1 ) {
			return;
		}

		foreach ( $wpdb->queries as $query_array ) {
			$query = prepare_wpdb_query( $query_array );

			$this->data_source->add_query( $query[0], $query[1], $query[2] );
		}
	}
}
