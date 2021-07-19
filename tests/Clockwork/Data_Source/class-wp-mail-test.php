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
			'headers' => [ 'irrelevant' ],
		] );

		$data_source->resolve( $request );

		$this->assertEquals( 'Failed to send an email', $request->log()->messages[0]['message'] );
		$this->assertEquals( 'Sending an email', $request->emailsData[0]['description'] );
		$this->assertSame( 0.0, $request->emailsData[0]['duration'] );
		$this->assertEquals( [
			'to' => 'fake@notreal.com',
			'subject' => 'Just another email',
			'headers' => [ 'irrelevant' ],
		], $request->emailsData[0]['data'] );
	}
}
