<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Log;
use Clockwork\Request\Request;

final class Errors extends DataSource {
	/**
	 * @var int
	 */
	private $error_reporting;

	/**
	 * @var array<string, array{type: int, message: string, file: null|string, line: null|int, suppressed: bool}>
	 */
	private $errors = [];

	/**
	 * @var null|callable
	 */
	private $original_handler;

	/**
	 * @var bool
	 */
	private $registered = false;

	/**
	 * @var null|self
	 */
	private static $instance;

	public function __construct() {
		$this->error_reporting = \error_reporting();
	}

	/**
	 * @return array<string, array{type: int, message: string, file: null|string, line: null|int, suppressed: bool}>
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	public function handle(
		int $type,
		string $message,
		?string $file = null,
		?int $line = null
	): bool {
		$this->record( $type, $message, $file, $line );

		if ( \is_callable( $this->original_handler ) ) {
			return ( $this->original_handler )( $type, $message, $file, $line );
		}

		return false;
	}

	public function reapply_filters(): void {
		$this->errors = \array_filter(
			$this->errors,
			function ( array $error ): bool {
				return $this->passesFilters( [ $error ] );
			}
		);
	}

	public function record( int $no, string $str, ?string $file = null, ?int $line = null ): void {
		$probably_suppressed = 0 === \error_reporting() && 0 !== $this->error_reporting;

		if ( $probably_suppressed ) {
			$should_log = $this->error_reporting & $no;
		} else {
			$should_log = \error_reporting() & $no;
		}

		if ( ! (bool) $should_log ) {
			return;
		}

		$error_array = [
			'type' => $no,
			'message' => $str,
			'file' => $file,
			'line' => $line,
			'suppressed' => $probably_suppressed,
		];

		if ( ! $this->passesFilters( [ $error_array ] ) ) {
			return;
		}

		$this->errors[ \hash( 'md5', \serialize( [ $no, $str, $file, $line ] ) ) ] = $error_array;
	}

	public function register(): void {
		if ( $this->registered ) {
			return;
		}

		// If there was an error before this plugin is loaded we will record it.
		$error = \error_get_last();

		if ( $error ) {
			$this->record( $error['type'], $error['message'], $error['file'], $error['line'] );
		}

		$original_handler = \set_error_handler( [ $this, 'handle' ] );

		if ( \is_callable( $original_handler ) ) {
			$this->set_original_handler( $original_handler );
		}

		$this->registered = true;
	}

	public function resolve( Request $request ): Request {
		$this->append_log( $request );

		return $request;
	}

	public function set_original_handler( callable $handler ): void {
		$this->original_handler = $handler;
	}

	public function unregister(): void {
		if ( ! $this->registered ) {
			return;
		}

		\restore_error_handler();

		$this->errors = [];
		$this->original_handler = null;
		$this->registered = false;
	}

	private function append_log( Request $request ): void {
		$log = new Log();

		foreach ( $this->errors as $error ) {
			$message = $error['message'];

			if ( $error['suppressed'] ) {
				$message .= ' (@-suppressed)';
			}

			$log_method = $this->log_method( $error['type'] );

			\call_user_func(
				[ $log, $log_method ],
				$message,
				[
					'type' => $this->friendly_type( $error['type'] ),
					'file' => $error['file'],
					'line' => $error['line'],
				]
			);
		}

		$request->log()->merge( $log );
	}

	private function friendly_type( $type ) {
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
			if ( \defined( $constant ) && \constant( $constant ) === $type ) {
				return $constant;
			}
		}

		return (string) $type;
	}

	private function log_method( $type ) {
		// Best guess based on rough cross reference of predefined constants docs and PSR abstract
		// logger docs. May want to revisit eventually.
		switch ( $type ) {
			case \E_ERROR:
			case \E_PARSE:
			case \E_CORE_ERROR:
			case \E_COMPILE_ERROR:
			case \E_USER_ERROR:
			case \E_RECOVERABLE_ERROR:
				return 'error';

			case \E_WARNING:
			case \E_CORE_WARNING:
			case \E_COMPILE_WARNING:
			case \E_USER_WARNING:
			case \E_DEPRECATED:
			case \E_USER_DEPRECATED:
				return 'warning';

			default:
				return 'notice';
		}
	}

	public static function get_instance(): self {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function set_instance( self $instance ): void {
		self::$instance = $instance;
	}
}
