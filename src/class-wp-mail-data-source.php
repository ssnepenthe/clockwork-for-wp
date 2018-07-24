<?php

namespace Clockwork_For_Wp;

use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\DataSource\DataSource;

class Wp_Mail_Data_Source extends DataSource {
	protected $emails;

	public function __construct( Timeline $emails = null ) {
		$this->emails = $emails ?: new Timeline();
	}

	public function resolve( Request $request ) {
		$request->emailsData = $this->emails->finalize();

		return $request;
	}

	public function listen_to_events() {
		add_filter( 'wp_mail', [ $this, 'record_email_sent' ] );
	}

	public function record_email_sent( $args ) {
		$this->emails->addEvent(
			'email_' . hash( 'md5', serialize( $args ) ),
			'Sending an email',
			null,
			null,
			$args
		);

		return $args;
	}
}
