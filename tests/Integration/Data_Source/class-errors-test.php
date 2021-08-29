<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

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

		$this->data_source->record( E_ERROR, $message, $file, $line );

		$this->resolve_request();

		$entry = $this->request->log()->messages[0];

		$this->assertEquals( 'it_correctly_records_error_data', $entry['message'] );
		$this->assertEquals( [
			'__type__' => 'array',
			'type' => 'E_ERROR',
			'file' => '/it/correctly/records/error/data',
			'line' => 42,
		], $entry['context'] );
		$this->assertEquals( 'error', $entry['level'] ); // @todo
	}

	/** @test */
	public function it_prevents_duplicates_from_being_recorded() {
		$message = __FUNCTION__;
		$file = '/' . str_replace( '_', '/', __FUNCTION__ );
		$line = 42;

		$this->data_source->record( E_ERROR, $message, $file, $line );
		$this->data_source->record( E_ERROR, $message, $file, $line );

		$this->resolve_request();

		$this->assertCount( 1, $this->request->log()->messages );
	}

	/** @test */
	public function it_only_logs_configured_error_levels() {
		// Adjust error reporting.
		$error_reporting = error_reporting();
		error_reporting( E_ERROR | E_WARNING );

		$data_source = new Errors();

		$message = __FUNCTION__;
		$file = '/' . str_replace( '_', '/', __FUNCTION__ );
		$line = 42;

		// Should be recorded.
		$data_source->record( E_ERROR, $message, $file, $line );
		$data_source->record( E_WARNING, $message, $file, $line );

		// Should not be recorded.
		$data_source->record( E_PARSE, $message, $file, $line );
		$data_source->record( E_NOTICE, $message, $file, $line );
		$data_source->record( E_CORE_ERROR, $message, $file, $line );
		$data_source->record( E_CORE_WARNING, $message, $file, $line );
		$data_source->record( E_COMPILE_ERROR, $message, $file, $line );
		$data_source->record( E_COMPILE_WARNING, $message, $file, $line );
		$data_source->record( E_USER_ERROR, $message, $file, $line );
		$data_source->record( E_USER_WARNING, $message, $file, $line );
		$data_source->record( E_USER_NOTICE, $message, $file, $line );
		$data_source->record( E_STRICT, $message, $file, $line );
		$data_source->record( E_RECOVERABLE_ERROR, $message, $file, $line );
		$data_source->record( E_DEPRECATED, $message, $file, $line );
		$data_source->record( E_USER_DEPRECATED, $message, $file, $line );

		$data_source->resolve( $this->request );

		// Restore error reporting.
		error_reporting( $error_reporting );

		$this->assertCount( 2, $this->request->log()->messages );
		$this->assertEquals( 'E_ERROR', $this->request->log()->messages[0]['context']['type'] );
		$this->assertEquals( 'E_WARNING', $this->request->log()->messages[1]['context']['type'] );
	}

	/** @test */
	public function it_correctly_guesses_when_error_has_been_suppressed() {
		$message = __FUNCTION__;
		$file = '/' . str_replace( '_', '/', __FUNCTION__ );
		$line = 42;

		$this->data_source->record( E_ERROR, $message, $file, $line );

		$this->resolve_request();

		$this->assertStringNotContainsString(
			'@-suppressed',
			$this->request->log()->messages[0]['message']
		);

		$error_reporting = error_reporting();
		error_reporting( E_ERROR );

		$this->setUp();

		error_reporting( $error_reporting );

		$this->data_source->record( E_ERROR, $message, $file, $line );

		$this->resolve_request();

		$this->assertStringContainsString(
			'@-suppressed',
			$this->request->log()->messages[0]['message']
		);
	}

	/** @test */
	public function it_correctly_chooses_log_level_based_on_error_type() {
		$message = __FUNCTION__;
		$file = '/' . str_replace( '_', '/', __FUNCTION__ );
		$line = 42;

		// @todo Data provider?
		$levels = [
			[ E_ERROR, 'error' ],
			[ E_PARSE, 'error' ],
			[ E_CORE_ERROR, 'error' ],
			[ E_COMPILE_ERROR, 'error' ],
			[ E_USER_ERROR, 'error' ],
			[ E_RECOVERABLE_ERROR, 'error' ],
			[ E_WARNING, 'warning' ],
			[ E_CORE_WARNING, 'warning' ],
			[ E_COMPILE_WARNING, 'warning' ],
			[ E_USER_WARNING, 'warning' ],
			[ E_DEPRECATED, 'warning' ],
			[ E_USER_DEPRECATED, 'warning' ],
			[ E_NOTICE, 'notice' ],
			[ E_USER_NOTICE, 'notice' ],
			[ E_STRICT, 'notice' ],
		];

		foreach ( $levels as [ $contant, $_ ] ) {
			$this->data_source->record( $contant, $message, $file, $line );
		}

		$this->resolve_request();
		$messages = $this->request->log()->messages;

		foreach ( $levels as $i => [ $_, $level ] ) {
			$this->assertSame( $level, $messages[ $i ]['level'] );
		}
	}
}
