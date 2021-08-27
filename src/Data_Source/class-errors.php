<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

// @todo Also handle exceptions?
class Errors extends DataSource implements Subscriber {
	protected $display;
	protected $error_reporting;
	protected $errors = [];
	protected $original_handler;
	protected $registered = false;

	public function __construct( $display = false, $error_reporting = E_ALL ) {
		$this->display = $display;
		$this->error_reporting = $error_reporting;
	}

	public function get_subscribed_events() : array {
		return [
			'cfw_pre_resolve' => [
				function() {
					$this->flush_errors();
				},
				Event_Manager::LATE_EVENT,
			],
		];
	}

	public function resolve( Request $request ) {
		$request->log()->merge( $this->get_errors_log() );

		return $request;
	}

	public function register() {
		if ( $this->registered ) {
			return;
		}

		$this->original_handler = set_error_handler( [ $this, 'handler' ] );

		$this->registered = true;
	}

	public function handler( $no, $str, $file = null, $line = null ) {
		$this->record_error( $no, $str, $file, $line );

		if ( is_callable( $this->original_handler ) ) {
			return call_user_func( $this->original_handler, $no, $str, $file, $line );
		}

		return false;
	}

	public function record_error( $no, $str, $file = null, $line = null ) {
		// @todo Consider only recording errors when true === $this->display?
		// MD5 is used in an attempt to prevent duplicates when recording last error on shutdown.
		$this->errors[ hash( 'md5', serialize( [ $no, $str, $file, $line ] ) ) ] = [
			'type' => $no,
			'message' => $str,
			'file' => $file,
			'line' => $line,
		];
	}

	protected function get_errors_log() {
		$log = new Log();

		foreach ( $this->errors as $error ) {
			if ( $this->should_log( $error['type'] ) ) {
				// @todo Logging by actual type instead of debug?
				$log->debug( $error['message'], [
					'type' => $this->friendly_type( $error['type'] ),
					'file' => $error['file'],
					'line' => $error['line'],
				] );
			}
		}

		return $log;
	}

	protected function flush_errors() {
		// MAJOR @todo!!!
		$guard = function() {
			// Because we want to avoid printing errors for clockwork requests...
			// @todo Cleaner handling.
			if ( '1' === get_query_var( 'cfw_app', null ) ) {
				return true;
			}

			if ( null !== get_query_var( 'cfw_id', null ) ) {
				return true;
			}

			if ( null !== get_query_var( 'cfw_auth', null ) ) {
				return true;
			}

			return false;
		};

		// @todo May be better to include special handling for json requests?
		if ( $guard() ) {
			return;
		}

		foreach ( $this->errors as $error ) {
			if ( $this->should_display( $error['type'] ) ) {
				$this->print_error( $error );
			}
		}
	}

	protected function print_error( $error ) {
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

	protected function should_display( $type ) {
		return $this->display && $this->should_log( $type );
	}

	protected function should_log( $type ) {
		return (bool) ( $this->error_reporting & $type );
	}
}
