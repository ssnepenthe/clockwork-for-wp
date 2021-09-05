<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

final class Logger_Chain {
	private $loggers;

	public function __construct( ...$loggers ) {
		$this->loggers = $loggers;
	}

	public function debug( $message, $group = false ): void {
		$this->each( __FUNCTION__, $message, $group );
	}

	public function error( $message ): void {
		$this->each( __FUNCTION__, $message );
	}

	public function error_multi_line( $message_lines ): void {
		$this->each( __FUNCTION__, $message_lines );
	}

	public function info( $message ): void {
		$this->each( __FUNCTION__, $message );
	}

	public function success( $message ): void {
		$this->each( __FUNCTION__, $message );
	}

	public function warning( $message ): void {
		$this->each( __FUNCTION__, $message );
	}

	private function each( $method, ...$args ): void {
		foreach ( $this->loggers as $logger ) {
			if ( \method_exists( $logger, $method ) ) {
				\call_user_func_array( [ $logger, $method ], $args );
			}
		}
	}
}
