<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Transients;
use Clockwork_For_Wp\Event_Management\Subscriber;

class Transients_Subscriber implements Subscriber {
	protected Transients $data_source;

	public function __construct( Transients $data_source ) {
		$this->data_source = $data_source;
	}

	public function get_subscribed_events(): array {
		return [
			'setted_transient' => 'on_setted_transient',
			'setted_site_transient' => 'on_setted_site_transient',
			'deleted_transient' => 'on_deleted_transient',
			'deleted_site_transient' => 'on_deleted_site_transient',
		];
	}

	public function on_deleted_site_transient( $transient ): void {
		$this->data_source->deleted( $transient, $is_site = true );
	}

	public function on_deleted_transient( $transient ): void {
		$this->data_source->deleted( $transient );
	}

	public function on_setted_site_transient( $transient, $value, $expiration ): void {
		$this->data_source->setted( $transient, $value, $expiration, $is_site = true );
	}

	public function on_setted_transient( $transient, $value, $expiration ): void {
		$this->data_source->setted( $transient, $value, $expiration );
	}
}
