<?php

namespace Clockwork_For_Wp\Tests\Clockwork\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Errors;
use PHPUnit\Framework\TestCase;

class Errors_Test extends TestCase {
	protected $data_source;
	protected $request;
	protected $request_resolved;

	protected function setUp() : void {
		$this->data_source = new Errors();
		$this->request = new Request();
		$this->request_resolved = false;
	}

	protected function tearDown() : void {
		$this->data_source = null;
		$this->request = null;
		$this->request_resolved = false;
	}

	protected function resolve_request() {
		if ( $this->request_resolved ) {
			return;
		}

		$this->data_source->resolve( $this->request );

		$this->request_resolved = true;
	}

	/** @test */
	public function it_correctly_records_error_data() {
		$message = __FUNCTION__;
		$file = '/' . str_replace( '_', '/', __FUNCTION__ );
		$line = 42;

		$this->data_source->record_error( E_ERROR, $message, $file, $line );

		$this->resolve_request();

		$entry = $this->request->log()->messages[0];

		$this->assertEquals( 'it_correctly_records_error_data', $entry['message'] );
		$this->assertEquals( [
			'__type__' => 'array',
			'type' => 'E_ERROR',
			'file' => '/it/correctly/records/error/data',
			'line' => 42,
		], $entry['context'] );
		$this->assertEquals( 'debug', $entry['level'] ); // @todo
	}

	/** @test */
	public function it_prevents_duplicates_from_being_recorded() {
		$message = __FUNCTION__;
		$file = '/' . str_replace( '_', '/', __FUNCTION__ );
		$line = 42;

		$this->data_source->record_error( E_ERROR, $message, $file, $line );
		$this->data_source->record_error( E_ERROR, $message, $file, $line );

		$this->resolve_request();

		$this->assertCount( 1, $this->request->log()->messages );
	}

	/** @test */
	public function it_only_logs_configured_error_levels() {
		$data_source = new Errors( false, E_ERROR | E_WARNING );

		$message = __FUNCTION__;
		$file = '/' . str_replace( '_', '/', __FUNCTION__ );
		$line = 42;

		// Should be recorded.
		$data_source->record_error( E_ERROR, $message, $file, $line );
		$data_source->record_error( E_WARNING, $message, $file, $line );

		// Should not be recorded.
		$data_source->record_error( E_PARSE, $message, $file, $line );
		$data_source->record_error( E_NOTICE, $message, $file, $line );
		$data_source->record_error( E_CORE_ERROR, $message, $file, $line );
		$data_source->record_error( E_CORE_WARNING, $message, $file, $line );
		$data_source->record_error( E_COMPILE_ERROR, $message, $file, $line );
		$data_source->record_error( E_COMPILE_WARNING, $message, $file, $line );
		$data_source->record_error( E_USER_ERROR, $message, $file, $line );
		$data_source->record_error( E_USER_WARNING, $message, $file, $line );
		$data_source->record_error( E_USER_NOTICE, $message, $file, $line );
		$data_source->record_error( E_STRICT, $message, $file, $line );
		$data_source->record_error( E_RECOVERABLE_ERROR, $message, $file, $line );
		$data_source->record_error( E_DEPRECATED, $message, $file, $line );
		$data_source->record_error( E_USER_DEPRECATED, $message, $file, $line );

		$data_source->resolve( $this->request );

		$this->assertCount( 2, $this->request->log()->messages );
		$this->assertEquals( 'E_ERROR', $this->request->log()->messages[0]['context']['type'] );
		$this->assertEquals( 'E_WARNING', $this->request->log()->messages[1]['context']['type'] );
	}
}
