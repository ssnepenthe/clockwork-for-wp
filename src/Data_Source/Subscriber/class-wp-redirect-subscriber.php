<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Wp_Redirect;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

/**
 * @internal
 */
final class Wp_Redirect_Subscriber implements Subscriber {
	private Wp_Redirect $data_source;

	public function __construct( Wp_Redirect $data_source ) {
		$this->data_source = $data_source;
	}

	public function get_subscribed_events(): array {
		return [
			'wp_redirect' => [ 'on_wp_redirect', Event_Manager::LATE_EVENT ],
			'wp_redirect_status' => [ 'on_wp_redirect_status', Event_Manager::LATE_EVENT ],
			'x_redirect_by' => [ 'on_x_redirect_by', Event_Manager::LATE_EVENT ],
		];
	}

	public function on_wp_redirect( $location ) {
		$this->data_source->record_wp_redirect_call();
		$this->data_source->set_filtered( 'location', $location );

		return $location;
	}

	public function on_wp_redirect_status( $status ) {
		$this->data_source->set_filtered( 'status', $status );

		return $status;
	}

	public function on_x_redirect_by( $x_redirect_by ) {
		$this->data_source->set_filtered( 'x-redirect-by', $x_redirect_by );
		$this->data_source->finalize_wp_redirect_call();

		return $x_redirect_by;
	}
}
