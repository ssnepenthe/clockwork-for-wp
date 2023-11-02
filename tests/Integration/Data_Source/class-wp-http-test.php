<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wp_Http;
use PHPUnit\Framework\TestCase;

class Wp_Http_Test extends TestCase {
	/** @test */
	public function it_only_records_http_request_start_when_meta_is_present_in_args(): void {
		$data_source = new Wp_Http();
		$request = new Request();

		$data_source->start_request( [] );

		$data_source->resolve( $request );

		$this->assertEmpty( $request->timelineData );
	}

	/** @test */
	public function it_correctly_records_http_request_start(): void {
		$data_source = new Wp_Http();
		$request = new Request();

		$args = $data_source->ensure_args_have_meta( [ 'test' => '123' ], 'https://example.com' );
		$data_source->start_request( $args );

		$data_source->resolve( $request );

		$this->assertEquals(
			'HTTP request for https://example.com',
			$request->timeline()->events[0]->description
		);
		$this->assertGreaterThan( 0, $request->timeline()->events[0]->end()->duration() );
	}

	/** @test */
	public function it_correctly_records_meta_error(): void {
		$data_source = new Wp_Http();
		$request = new Request();

		$data_source->finish_request( [ 'irrelevant' ], [ 'test' => '123' ] );

		$data_source->resolve( $request );

		$this->assertEquals(
			'Error in HTTP data source - meta is not set in provided args',
			$request->log()->messages[0]['message']
		);
	}

	/** @test */
	public function it_correctly_records_request_failure(): void {
		$data_source = new Wp_Http();
		$request = new Request();

		$args = $data_source->ensure_args_have_meta( [ 'test' => '123' ], 'https://example.com' );
		// $data_source->start_request( $args );
		$data_source->finish_request( [
			'error' => [
				'errors' => [ 'one' => [ 'two' ] ],
				'error_data' => [ 'one' => [ 'three' ] ],
			],
			'response' => null,
		], $args );

		$data_source->resolve( $request );

		$this->assertEquals(
			'HTTP request for https://example.com failed',
			$request->log()->messages[0]['message']
		);
	}

	/** @test */
	public function it_correctly_records_request_success(): void {
		$data_source = new Wp_Http();
		$request = new Request();

		$args = $data_source->ensure_args_have_meta( [ 'test' => '123' ], 'https://example.com' );
		$data_source->start_request( $args );
		$data_source->finish_request( [
			'error' => null,
			'response' => [
				'body' => 'irrelevant body',
				'cookies' => [ 'irrelevant cookie' ],
				'headers' => [ 'irrelevant header' ],
				'status' => 200,
			],
		], $args );

		$data_source->resolve( $request );

		$this->assertEquals(
			'HTTP request for https://example.com',
			$request->timeline()->events[0]->description
		);
		$this->assertGreaterThan( 0, $request->timeline()->events[0]->duration() );
		$this->assertEquals(
			'HTTP request for https://example.com succeeded',
			$request->log()->messages[0]['message']
		);
	}
}
