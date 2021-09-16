<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Errors;
use PHPUnit\Framework\TestCase;

class Errors_Test extends TestCase {
	private $previous_error_reporting;

	protected function tearDown() : void {
		if ( $this->previous_error_reporting ) {
			error_reporting( $this->previous_error_reporting );
			$this->previous_error_reporting = null;
		}
	}

	/** @test */
	public function it_correctly_records_error_data() {
		$data_source = $this->make_data_source( E_ERROR );
		$request = new Request();
		$input = $this->get_function_specific_test_input();

		$data_source->record( E_ERROR, $input['message'], $input['file'], $input['line'] );

		$data_source->resolve( $request );

		$entry = $request->log()->messages[0];

		$this->assertEquals( $input['message'], $entry['message'] );
		$this->assertEquals( [
			'__type__' => 'array',
			'type' => 'E_ERROR',
			'file' => $input['file'],
			'line' => $input['line'],
		], $entry['context'] );
		$this->assertEquals( 'error', $entry['level'] );
	}

	/** @test */
	public function it_prevents_duplicates_from_being_recorded() {
		$data_source = $this->make_data_source( E_ERROR );
		$request = new Request();
		$input = $this->get_function_specific_test_input();

		$data_source->record( E_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_ERROR, $input['message'], $input['file'], $input['line'] );

		$data_source->resolve( $request );

		$this->assertCount( 1, $request->log()->messages );
	}

	/** @test */
	public function it_only_logs_configured_error_levels() {
		$data_source = $this->make_data_source( E_ERROR | E_WARNING );
		$request = new Request();
		$input = $this->get_function_specific_test_input();

		// Should be recorded.
		$data_source->record( E_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_WARNING, $input['message'], $input['file'], $input['line'] );

		// Should not be recorded.
		$data_source->record( E_PARSE, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_NOTICE, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_CORE_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_CORE_WARNING, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_COMPILE_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_COMPILE_WARNING, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_USER_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_USER_WARNING, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_USER_NOTICE, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_STRICT, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_RECOVERABLE_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_DEPRECATED, $input['message'], $input['file'], $input['line'] );
		$data_source->record( E_USER_DEPRECATED, $input['message'], $input['file'], $input['line'] );

		$data_source->resolve( $request );

		$this->assertCount( 2, $request->log()->messages );
		$this->assertEquals( 'E_ERROR', $request->log()->messages[0]['context']['type'] );
		$this->assertEquals( 'E_WARNING', $request->log()->messages[1]['context']['type'] );
	}

	/** @test */
	public function it_does_not_mark_errors_as_suppressed_by_default() {
		$data_source = $this->make_data_source( E_ERROR );
		$request = new Request();
		$input = $this->get_function_specific_test_input();

		$data_source->record( E_ERROR, $input['message'], $input['file'], $input['line'] );

		$data_source->resolve( $request );

		$this->assertStringNotContainsString(
			'@-suppressed',
			$request->log()->messages[0]['message']
		);
	}

	/** @test */
	public function it_correctly_guesses_when_error_has_been_suppressed() {
		$data_source = $this->make_data_source( E_ERROR );
		$request = new Request();
		$input = $this->get_function_specific_test_input();

		// When current error reporting setting is 0 and initial error reporting setting is not, we assume error suppression.
		error_reporting( 0 );

		$data_source->record( E_ERROR, $input['message'], $input['file'], $input['line'] );

		$data_source->resolve( $request );

		$this->assertStringContainsString(
			'@-suppressed',
			$request->log()->messages[0]['message']
		);
	}

	/** @test */
	public function it_correctly_chooses_log_level_based_on_error_type() {
		$data_source = $this->make_data_source( E_ALL );
		$request = new Request();
		$input = $this->get_function_specific_test_input();

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
			$data_source->record( $contant, $input['message'], $input['file'], $input['line'] );
		}

		$data_source->resolve( $request );
		$messages = $request->log()->messages;

		foreach ( $levels as $i => [ $_, $level ] ) {
			$this->assertSame( $level, $messages[ $i ]['level'] );
		}
	}

	private function make_data_source( int $error_reporting ): Errors {
		if ( $error_reporting ) {
			$this->previous_error_reporting = error_reporting( $error_reporting );
		}

		return new Errors();
	}

	private function get_function_specific_test_input(): array {
		// This data is all arbitrary - we are just verifying that what goes into the data source comes back out of the request...
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 );

		// So lets use the test method name name as our error message and file path...
		$function = $backtrace[1]['function'];

		// And the calling line from that test method as our line number...
		$line = $backtrace[0]['line'];

		return [
			'message' => $function,
			'file' => '/' . str_replace( '_', '/', $function ),
			'line' => $line,
		];
	}
}
