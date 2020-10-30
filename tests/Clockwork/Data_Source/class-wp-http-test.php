<?php

namespace Clockwork_For_Wp\Tests\Clockwork\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wp_Http;
use PHPUnit\Framework\TestCase;

class Wp_Http_Test extends TestCase {
	/** @test */
	public function it_only_records_http_request_start_when_meta_is_present_in_args() {
		$data_source = new Wp_Http();
		$request = new Request();

		$data_source->start_request( [] );

		$data_source->resolve( $request );

		$this->assertEmpty( $request->timelineData );
	}

	/** @test */
	public function it_correctly_records_http_request_start() {
		$data_source = new Wp_Http();
		$request = new Request();

		$args = $data_source->ensure_args_have_meta( [ 'test' => '123' ], 'https://example.com' );
		$data_source->start_request( $args );

		$data_source->resolve( $request );

		$key = "http_{$args['_cfw_meta']['fingerprint']}";

		$this->assertArrayHasKey( $key, $request->timelineData );
		$this->assertEquals(
			'HTTP request for https://example.com',
			$request->timelineData[ $key ]['description']
		);
	}

	/** @test */
	public function it_correctly_records_meta_error() {
		$data_source = new Wp_Http();
		$request = new Request();

		$data_source->finish_request( [ 'irrelevant' ], [ 'test' => '123' ] );

		$data_source->resolve( $request );

		$this->assertEquals(
			'Error in HTTP data source - meta is not set in provided args',
			$request->log[0]['message']
		);
	}

	/** @test */
	public function it_correctly_records_request_failure() {
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
			$request->log[0]['message']
		);
	}

	/** @test */
	public function it_correctly_records_request_success() {
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

		$this->assertArrayHasKey(
			"http_{$args['_cfw_meta']['fingerprint']}",
			$request->timelineData
		);
		$this->assertEquals(
			'HTTP request for https://example.com succeeded',
			$request->log[0]['message']
		);
	}
}
