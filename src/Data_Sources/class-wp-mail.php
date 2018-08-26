<?php

namespace Clockwork_For_Wp\Data_Sources;

use WP_Error;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\DataSource\DataSource;

class Wp_Mail extends DataSource {
	protected $emails;
	protected $log;

	public function __construct( Timeline $emails = null, Log $log = null ) {
		$this->emails = $emails ?: new Timeline();
		$this->log = $log ?: new Log();
	}

	public function resolve( Request $request ) {
		$request->emailsData = array_merge( $request->emailsData, $this->emails->finalize() );
		$request->log = array_merge( $request->log, $this->log->toArray() );

		return $request;
	}

	public function on_wp_mail( $args ) {
		$this->add_event( $args );

		return $args;
	}

	public function on_wp_mail_failed( WP_Error $error ) {
		$this->log_error( $error );
	}

	public function add_event( $args ) {
		$to = isset( $args['to'] ) ? $args['to'] : '';

		$this->emails->addEvent(
			'email_' . hash( 'md5', serialize( $args ) ),
			'Sending an email',
			null,
			null,
			[
				// @todo If we want the from address we would use the 'wp_mail_from' filter, but this isn't actually displayed on the clockwork frontend.
				// 'from' => '',
				'to' => is_array( $to ) ? implode( ', ', $to ) : $to,
				'subject' => isset( $args['subject'] ) ? $args['subject'] : '',
				// @todo Headers can be a string or array - should we attempt to normalize?
				'headers' => isset( $args['headers'] ) ? $args['headers'] : [],
			]
		);

		return $args;
	}

	public function log_error( WP_Error $error ) {
		$data = $error->get_error_data();

		if ( isset( $data['message'] ) ) {
			// @todo Should we include the whole message body? Could get long, esp. for html emails.
			$data['message'] = wp_trim_words( $data['message'] );
		}

		$data['error_message'] = $error->get_error_message();

		$this->log->error( 'Failed to send an email', $data );
	}
}
