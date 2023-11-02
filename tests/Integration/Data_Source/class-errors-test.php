<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Errors;
use PHPUnit\Framework\TestCase;

class Errors_Test extends TestCase {
	private $previous_error_reporting;

	protected function tearDown(): void {
		if ( $this->previous_error_reporting ) {
			error_reporting( $this->previous_error_reporting );
			$this->previous_error_reporting = null;
		}
	}

	public function test_handle_returns_false_by_default(): void {
		$data_source = $this->make_data_source( \E_ERROR );
		$input = $this->get_function_specific_test_input();

		$this->assertFalse(
			$data_source->handle( \E_ERROR, $input['message'], $input['file'], $input['line'] )
		);
	}

	public function test_handle_invokes_original_handler_if_callable(): void {
		$data_source = $this->make_data_source( \E_ERROR );
		$input = $this->get_function_specific_test_input();
		$data_source->set_original_handler(
			function ( $type, $message, $file, $line ) use ( $input ) {
				$this->assertSame( \E_ERROR, $type );
				$this->assertSame( $input['message'], $message );
				$this->assertSame( $input['file'], $file );
				$this->assertSame( $input['line'], $line );

				return true;
			}
		);

		$this->assertTrue(
			$data_source->handle( \E_ERROR, $input['message'], $input['file'], $input['line'] )
		);
	}

	public function test_reapply_filters(): void {
		$data_source = $this->make_data_source( \E_ERROR );
		$input = $this->get_function_specific_test_input();

		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] + 1 );

		// With no filters, both errors should be recorded.
		$this->assertCount( 2, $data_source->get_errors() );

		$data_source->addFilter( fn( $error ) => $error['line'] <= $input['line'] );
		$data_source->reapply_filters();
		$errors = $data_source->get_errors();

		// With filter, the second error should have been discarded.
		$this->assertCount( 1, $errors );
		$this->assertSame( $input['line'], array_shift( $errors )['line'] );
	}

	public function test_record_flags_errors_as_probably_suppressed(): void {
		$data_source = $this->make_data_source( \E_ERROR );
		$input = $this->get_function_specific_test_input();

		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] );

		// When current error reporting setting is 0 and initial error reporting setting is not, we assume error suppression.
		$error_reporting = error_reporting( 0 );
		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] + 1 );
		error_reporting( $error_reporting );

		$errors = $data_source->get_errors();

		$this->assertCount( 2, $errors );
		$this->assertFalse( array_shift( $errors )['suppressed'] );
		$this->assertTrue( array_shift( $errors  )['suppressed'] );
	}

	public function test_record_discards_errors_based_on_current_error_reporting(): void {
		$data_source = $this->make_data_source( \E_ERROR );
		$input = $this->get_function_specific_test_input();

		$error_reporting = error_reporting( \E_ERROR | \E_WARNING );

		// Should be recorded.
		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_WARNING, $input['message'], $input['file'], $input['line'] );

		// Should not be recorded.
		$data_source->record( \E_PARSE, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_NOTICE, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_CORE_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_CORE_WARNING, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_COMPILE_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record(
			\E_COMPILE_WARNING,
			$input['message'],
			$input['file'],
			$input['line']
		);
		$data_source->record( \E_USER_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_USER_WARNING, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_USER_NOTICE, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_STRICT, $input['message'], $input['file'], $input['line'] );
		$data_source->record(
			\E_RECOVERABLE_ERROR,
			$input['message'],
			$input['file'],
			$input['line']
		);
		$data_source->record( \E_DEPRECATED, $input['message'], $input['file'], $input['line'] );
		$data_source->record(
			\E_USER_DEPRECATED,
			$input['message'],
			$input['file'],
			$input['line']
		);

		error_reporting( $error_reporting );

		$errors = $data_source->get_errors();

		$this->assertCount( 2, $errors );
		$this->assertSame( \E_ERROR, array_shift( $errors )['type'] );
		$this->assertSame( \E_WARNING, array_shift( $errors )['type'] );
	}

	public function test_record_discards_suppressed_errors_based_on_original_error_reporting(): void {
		$data_source = $this->make_data_source( \E_ERROR | \E_WARNING );
		$input = $this->get_function_specific_test_input();

		// Errors are flagged as suppressed when error reporting is currently set to 0 but was set to something else when error handle instance was created.
		$error_reporting = error_reporting( 0 );

		// Should be recorded.
		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_WARNING, $input['message'], $input['file'], $input['line'] );

		// Should not be recorded.
		$data_source->record( \E_PARSE, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_NOTICE, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_CORE_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_CORE_WARNING, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_COMPILE_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record(
			\E_COMPILE_WARNING,
			$input['message'],
			$input['file'],
			$input['line']
		);
		$data_source->record( \E_USER_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_USER_WARNING, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_USER_NOTICE, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_STRICT, $input['message'], $input['file'], $input['line'] );
		$data_source->record(
			\E_RECOVERABLE_ERROR,
			$input['message'],
			$input['file'],
			$input['line']
		);
		$data_source->record( \E_DEPRECATED, $input['message'], $input['file'], $input['line'] );
		$data_source->record(
			\E_USER_DEPRECATED,
			$input['message'],
			$input['file'],
			$input['line']
		);

		error_reporting( $error_reporting );

		$errors = $data_source->get_errors();

		$this->assertCount( 2, $errors );
		$this->assertSame( \E_ERROR, array_shift( $errors )['type'] );
		$this->assertSame( \E_WARNING, array_shift( $errors )['type'] );
	}

	public function test_record_discards_errors_based_on_user_defined_callbacks(): void {
		$data_source = $this->make_data_source( \E_ERROR );
		$input = $this->get_function_specific_test_input();
		$data_source->addFilter( fn( $error ) => $error['line'] <= $input['line'] );

		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] + 1 );

		$errors = $data_source->get_errors();

		$this->assertCount( 1, $errors );
		$this->assertSame( $input['line'], array_shift( $errors )['line'] );
	}

	public function test_record_prevents_duplicate_errors_from_being_recorded(): void {
		$data_source = $this->make_data_source( \E_ERROR );
		$input = $this->get_function_specific_test_input();

		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] );
		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] );

		$this->assertCount( 1, $data_source->get_errors() );
	}

	public function test_resolve_adds_error_suppression_note_to_message(): void {
		$data_source = $this->make_data_source( \E_ERROR );
		$input = $this->get_function_specific_test_input();

		$error_reporting = error_reporting( 0 );
		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] );
		error_reporting( $error_reporting );

		$request = $data_source->resolve( new Request() );

		$this->assertStringContainsString(
			'@-suppressed',
			$request->log()->messages[0]['message']
		);
	}

	/** @dataProvider provides_types_to_levels */
	public function test_resolve_correctly_sets_level_based_on_error_type( $type, $level ): void {
		$data_source = $this->make_data_source( \E_ALL );
		$input = $this->get_function_specific_test_input();

		$data_source->record( $type, $input['message'], $input['file'], $input['line'] );

		$request = $data_source->resolve( new Request() );
		$messages = $request->log()->messages;

		$this->assertCount( 1, $messages );
		$this->assertSame( $level, $messages[0]['level'] );
	}

	/** @dataProvider provides_types_to_labels */
	public function test_resolve_converts_error_type_int_to_human_readable_string( $type, $label ): void {
		$data_source = $this->make_data_source( \E_ALL );
		$input = $this->get_function_specific_test_input();

		$data_source->record( $type, $input['message'], $input['file'], $input['line'] );

		$request = $data_source->resolve( new Request() );
		$messages = $request->log()->messages;

		$this->assertCount( 1, $messages );
		$this->assertSame( $label, $messages[0]['context']['type'] );
	}

	public function test_resolve_stores_data_on_request(): void {
		$data_source = $this->make_data_source( \E_ALL );
		$input = $this->get_function_specific_test_input();
		$data_source->record( \E_ERROR, $input['message'], $input['file'], $input['line'] );

		$request = $data_source->resolve( new Request() );
		$messages = $request->log()->messages;

		$this->assertCount( 1, $messages );

		$message = $messages[0];

		$this->assertSame( $input['message'], $message['message'] );
		$this->assertSame( [
			'__type__' => 'array',
			'type' => 'E_ERROR',
			'file' => $input['file'],
			'line' => $input['line'],
		], $message['context'] );
		$this->assertSame( 'error', $message['level'] );
	}

	public function provides_types_to_levels() {
		yield [ \E_ERROR, 'error' ];
		yield [ \E_PARSE, 'error' ];
		yield [ \E_CORE_ERROR, 'error' ];
		yield [ \E_COMPILE_ERROR, 'error' ];
		yield [ \E_USER_ERROR, 'error' ];
		yield [ \E_RECOVERABLE_ERROR, 'error' ];
		yield [ \E_WARNING, 'warning' ];
		yield [ \E_CORE_WARNING, 'warning' ];
		yield [ \E_COMPILE_WARNING, 'warning' ];
		yield [ \E_USER_WARNING, 'warning' ];
		yield [ \E_DEPRECATED, 'warning' ];
		yield [ \E_USER_DEPRECATED, 'warning' ];
		yield [ \E_NOTICE, 'notice' ];
		yield [ \E_USER_NOTICE, 'notice' ];
		yield [ \E_STRICT, 'notice' ];
	}

	public function provides_types_to_labels() {
		yield [ \E_ERROR, 'E_ERROR' ];
		yield [ \E_PARSE, 'E_PARSE' ];
		yield [ \E_CORE_ERROR, 'E_CORE_ERROR' ];
		yield [ \E_COMPILE_ERROR, 'E_COMPILE_ERROR' ];
		yield [ \E_USER_ERROR, 'E_USER_ERROR' ];
		yield [ \E_RECOVERABLE_ERROR, 'E_RECOVERABLE_ERROR' ];
		yield [ \E_WARNING, 'E_WARNING' ];
		yield [ \E_CORE_WARNING, 'E_CORE_WARNING' ];
		yield [ \E_COMPILE_WARNING, 'E_COMPILE_WARNING' ];
		yield [ \E_USER_WARNING, 'E_USER_WARNING' ];
		yield [ \E_DEPRECATED, 'E_DEPRECATED' ];
		yield [ \E_USER_DEPRECATED, 'E_USER_DEPRECATED' ];
		yield [ \E_NOTICE, 'E_NOTICE' ];
		yield [ \E_USER_NOTICE, 'E_USER_NOTICE' ];
		yield [ \E_STRICT, 'E_STRICT' ];
	}

	private function make_data_source( int $error_reporting ): Errors {
		if ( $error_reporting ) {
			$this->previous_error_reporting = error_reporting( $error_reporting );
		}

		return new Errors();
	}

	private function get_function_specific_test_input(): array {
		// This data is all arbitrary - we are just verifying that what goes into the data source comes back out of the request...
		$backtrace = debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS, 2 );

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
