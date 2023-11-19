<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Wp_Mail;
use WP_Error;
use WpEventDispatcher\SubscriberInterface;

use function Clockwork_For_Wp\wp_error_to_array;

/**
 * @internal
 */
final class Wp_Mail_Subscriber implements SubscriberInterface {
	private Wp_Mail $data_source;

	public function __construct( Wp_Mail $data_source ) {
		$this->data_source = $data_source;
	}

	public function getSubscribedEvents(): array {
		return [
			'wp_mail_failed' => 'on_wp_mail_failed',
			'wp_mail' => 'on_wp_mail',
		];
	}

	public function on_wp_mail( $args ) {
		// @todo Prepare mail args helper?
		$this->data_source->record_send( $args );

		return $args;
	}

	public function on_wp_mail_failed( WP_Error $error ): void {
		// @todo Truncate field within error_data.
		$data = wp_error_to_array( $error );

		$this->data_source->record_failure( $data );
	}
}
