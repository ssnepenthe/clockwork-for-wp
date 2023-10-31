<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline\Timeline;
use Clockwork_For_Wp\Data_Source\Subscriber\Wp_Mail_Subscriber;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Provides_Subscriber;

// @todo Use phpmailer_init hook to get more details?
final class Wp_Mail extends DataSource implements Provides_Subscriber {
	private $emails;

	private $log;

	public function __construct( ?Timeline $emails = null, ?Log $log = null ) {
		$this->emails = $emails ?: new Timeline();
		$this->log = $log ?: new Log();
	}

	public function create_subscriber(): Subscriber {
		return new Wp_Mail_Subscriber( $this );
	}

	public function record_failure( $context ): void {
		$this->log->error( 'Failed to send an email', $context );
	}

	public function record_send( $args ): void {
		$to = $args['to'] ?? '';
		$subject = $args['subject'] ?? '';
		$headers = $args['headers'] ?? [];
		$time = \microtime( true );

		$this->emails->event(
			'Sending an email',
			[
				'name' => 'email_' . \hash( 'md5', \serialize( $args ) ),
				'data' => \compact( 'to', 'subject', 'headers' ),
				'start' => $time,
				'end' => $time,
			]
		);
	}

	public function resolve( Request $request ) {
		$request->emailsData = \array_merge( $request->emailsData, $this->emails->finalize() );
		$request->log()->merge( $this->log );

		return $request;
	}
}
