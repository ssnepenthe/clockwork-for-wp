<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Cli_Data_Collection;

use WP_CLI\Loggers\Execution;

final class Recorder extends Execution {
	public function ob_start_callback( $str ) {
		$this->write( \STDOUT, $str );

		return $str;
	}
}
