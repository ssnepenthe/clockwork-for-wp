<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork_For_Wp\Event_Management\Event_Manager;
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

	public function subscribe_to_events( Event_Manager $event_manager ) : void {
		$event_manager
			->on( 'wp_mail_failed', function( \WP_Error $error ) {
				// @todo Truncate field within error_data.
				$data = wp_error_to_array( $error );

				$this->record_failure( $data );
			} )
			->on( 'wp_mail', function( $args ) {
				// @todo Prepare mail args helper?
				$this->record_send( $args );

				return $args;
			} );
	}

	public function resolve( Request $request ) {
		$request->emailsData = array_merge( $request->emailsData, $this->emails->finalize() );
		$request->log = array_merge( $request->log, $this->log->toArray() );

		return $request;
	}

	public function record_failure( $context ) {
		$this->log->error( 'Failed to send an email', $context );
	}

	public function record_send( $args ) {
		$to = isset( $args['to'] ) ? $args['to'] : '';
		$subject = isset( $args['subject'] ) ? $args['subject'] : '';
		$headers = isset( $args['headers'] ) ? $args['headers'] : [];

		$this->emails->addEvent(
			'email_' . hash( 'md5', serialize( $args ) ),
			'Sending an email',
			null,
			null,
			compact( 'to', 'subject', 'headers' )
		);
	}
}
