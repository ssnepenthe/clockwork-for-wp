<?php

namespace Clockwork_For_Wp\Wp_Cli;

use WP_CLI\Loggers\Execution;

class Recorder extends Execution {
	public function ob_start_callback( $str ) {
		$this->write( STDOUT, $str );

		return $str;
	}
}
