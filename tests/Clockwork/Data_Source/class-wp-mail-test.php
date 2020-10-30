<?php

namespace Clockwork_For_Wp\Tests\Clockwork\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wp_Mail;
use PHPUnit\Framework\TestCase;

class Wp_Mail_Test extends TestCase {
	/** @test */
	public function it_correctly_records_wp_mail_data() {
		$data_source = new Wp_Mail();
		$request = new Request();

		$data_source->record_failure( [
			'errors' => [ 'one' => [ 'two' ] ],
			'error_data' => [ 'one' => [ 'three' ] ],
		] );
		$data_source->record_send( $args = [
			'to' => 'fake@notreal.com',
			'subject' => 'Just another email',
			'headers' => [ 'irellevant' ],
		] );
		$event_key = 'email_' . hash( 'md5', serialize( $args ) );

		$data_source->resolve( $request );

		$this->assertEquals( 'Failed to send an email', $request->log[0]['message'] );
		$this->assertArrayHasKey( $event_key, $request->emailsData );
	}
}
