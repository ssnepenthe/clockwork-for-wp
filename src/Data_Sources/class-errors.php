<?php

namespace Clockwork_For_Wp\Data_Sources;

use Psr\Log\LogLevel;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Errors extends DataSource {
	protected $display;
	protected $errors;
	protected $log;
	protected $original_handler;

	public function __construct( Log $log = null ) {
		$this->log = $log ?: new Log();
		$this->original_handler = set_error_handler( [ $this, 'handle' ] );
		$this->errors = [];

		// If display_errors is set, output can potentially prevent necessary headers
		// from being sent. For this reason we will prevent errors from being
		// displayed immediately, outputting them manually on shutdown.
		$this->display = filter_var( ini_get( 'display_errors' ), FILTER_VALIDATE_BOOLEAN );
		ini_set( 'display_errors', 0 );
	}

	public function resolve( Request $request ) {
		$request->log = array_merge( $request->log, $this->log->toArray() );

		return $request;
	}

	public function print_recorded_errors() {
		if ( $this->is_clockwork_request() ) {
			return;
		}

		$last_error = error_get_last();

		if ( null !== $last_error && $this->should_display( $last_error['type'] ) ) {
			$this->record_error(
				$last_error['type'],
				$last_error['message'],
				$last_error['file'],
				$last_error['line']
			);
		}

		if ( ! $this->display ) {
			return;
		}

		foreach ( $this->errors as $error ) {
			$this->display_error( $error );
		}
	}

	public function handle( $no, $str, $file = null, $line = null ) {
		if ( $this->should_log( $no ) ) {
			$this->log->error( $str, [
				'type' => $this->friendly_type( $no ),
				'file' => $file,
				'line' => $line,
			] );
		}

		if ( $this->should_display( $no ) ) {
			$this->record_error( $no, $str, $file, $line );
		}

		if ( is_callable( $this->original_handler ) ) {
			return call_user_func( $this->original_handler, $no, $str, $file, $line );
		}

		return false;
	}

	protected function display_error( $error ) {
		if ( function_exists( 'xdebug_print_function_stack' ) ) {
			xdebug_print_function_stack( sprintf(
				'%s: %s in %s on line %d. Output triggered',
				$this->friendly_label( $error['type'] ),
				$error['message'],
				$error['file'],
				$error['line']
			) );
		} else {
			printf(
				'<br /><b>%s</b>: %s in <b>%s</b> on line <b>%d</b><br />',
				htmlentities( $this->friendly_label( $error['type'] ) ),
				htmlentities( $error['message'] ),
				htmlentities( $error['file'] ),
				$error['line']
			);
		}
	}

	protected function friendly_label( $type ) {
		// @todo Need to verify if this is thorough enough...
		switch ( $type ) {
			case E_COMPILE_WARNING:
			case E_CORE_WARNING:
			case E_USER_WARNING:
			case E_WARNING:
				return 'Warning';

			case E_NOTICE:
			case E_USER_NOTICE:
				return 'Notice';

			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				return 'Deprecated';

			case E_RECOVERABLE_ERROR:
				return 'Catchable fatal error';

			default:
				return 'Fatal error';
		}
	}

	protected function friendly_type( $type ) {
		foreach ( [
			'E_ERROR',
			'E_WARNING',
			'E_PARSE',
			'E_NOTICE',
			'E_CORE_ERROR',
			'E_CORE_WARNING',
			'E_COMPILE_ERROR',
			'E_COMPILE_WARNING',
			'E_USER_ERROR',
			'E_USER_WARNING',
			'E_USER_NOTICE',
			'E_STRICT',
			'E_RECOVERABLE_ERROR',
			'E_DEPRECATED',
			'E_USER_DEPRECATED',
			'E_ALL',
		] as $constant ) {
			if ( defined( $constant ) && $type === constant( $constant ) ) {
				return $constant;
			}
		}

		return (string) $type;
	}

	protected function is_clockwork_request() {
		// Because we want to avoid printing errors for clockwork requests...
		// @todo Cleaner handling.
		if ( '1' === get_query_var( 'cfw_app', null ) ) {
			return true;
		}

		if ( null !== get_query_var( 'cfw_id', null ) ) {
			return true;
		}

		return false;
	}

	protected function record_error( $no, $str, $file = null, $line = null ) {
		// @todo Consider only recording errors when true === $this->display?
		// MD5 is used in an attempt to prevent duplicates when recording last error on shutdown.
		$this->errors[ hash( 'md5', serialize( [ $no, $str, $file, $line ] ) ) ] = [
			'type' => $no,
			'message' => $str,
			'file' => $file,
			'line' => $line,
		];
	}

	protected function should_display( $type ) {
		return $this->display && $this->should_log( $type );
	}

	protected function should_log( $type ) {
		return (bool) ( error_reporting() & $type );
	}
}
