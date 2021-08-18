<?php

namespace Clockwork_For_Wp\Wp_Cli;

class Logger_Chain {
	protected $loggers;

	public function __construct( ...$loggers ) {
		$this->loggers = $loggers;
	}

	public function debug( $message, $group = false ) {
		$this->each( __FUNCTION__, $message, $group );
	}

	public function error( $message ) {
		$this->each( __FUNCTION__, $message );
	}

	public function error_multi_line( $message_lines ) {
		$this->each( __FUNCTION__, $message_lines );
	}

	public function info( $message ) {
		$this->each( __FUNCTION__, $message );
	}

	public function success( $message ) {
		$this->each( __FUNCTION__, $message );
	}

	public function warning( $message ) {
		$this->each( __FUNCTION__, $message );
	}

	protected function each( $method, ...$args ) {
		foreach ( $this->loggers as $logger ) {
			if ( \method_exists( $logger, $method ) ) {
				\call_user_func_array( [ $logger, $method ], $args );
			}
		}
	}
}
