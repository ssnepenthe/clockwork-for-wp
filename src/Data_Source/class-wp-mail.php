<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline\Timeline;
use Clockwork_For_Wp\Event_Management\Subscriber;

use function Clockwork_For_Wp\wp_error_to_array;

// @todo Use phpmailer_init hook to get more details?
class Wp_Mail extends DataSource implements Subscriber {
	protected $emails;
	protected $log;

	public function __construct( Timeline $emails = null, Log $log = null ) {
		$this->emails = $emails ?: new Timeline();
		$this->log = $log ?: new Log();
	}

	public function get_subscribed_events() : array {
		return [
			'wp_mail_failed' => function( \WP_Error $error ) {
				// @todo Truncate field within error_data.
				$data = wp_error_to_array( $error );

				$this->record_failure( $data );
			},
			'wp_mail' => function( $args ) {
				// @todo Prepare mail args helper?
				$this->record_send( $args );

				return $args;
			},
		];
	}

	public function resolve( Request $request ) {
		$request->emailsData = array_merge( $request->emailsData, $this->emails->finalize() );
		$request->log()->merge( $this->log );

		return $request;
	}

	public function record_failure( $context ) {
		$this->log->error( 'Failed to send an email', $context );
	}

	public function record_send( $args ) {
		$to = isset( $args['to'] ) ? $args['to'] : '';
		$subject = isset( $args['subject'] ) ? $args['subject'] : '';
		$headers = isset( $args['headers'] ) ? $args['headers'] : [];
		$time = microtime( true );

		$this->emails->event( 'Sending an email', [
			'name' => 'email_' . hash( 'md5', serialize( $args ) ),
			'data' => compact( 'to', 'subject', 'headers' ),
			'start' => $time,
			'end' => $time,
		] );
	}
}
