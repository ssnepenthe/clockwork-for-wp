<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\XdebugDataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Subscriber\Xdebug_Subscriber;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Provides_Subscriber;

/**
 * Adapted from the Xdebug datasource bundled with Clockwork.
 *
 * Key difference is that the profiler filename is resolved on the WordPress init hook.
 *
 * It appears that shutdown functions can cause secondary profiler files to be created. Since we
 * would be resolving Clockwork requests in a shutdown function, the bundled xdebug datasource
 * appears to be grabbing profiling data for the previously executed shutdown function.
 *
 * @todo Need to look into this more - is it a bug or intended behavior?
 */
final class Xdebug extends XdebugDataSource implements Provides_Subscriber {
	private $profiler_filename;

	public function create_subscriber(): Subscriber {
		return new Xdebug_Subscriber( $this );
	}

	public function resolve( Request $request ) {
		if ( \is_string( $this->profiler_filename ) ) {
			$request->xdebug = [
				'profile' => $this->profiler_filename,
			];
		}

		return $request;
	}

	public function set_profiler_filename( string $filename ) {
		$this->profiler_filename = $filename;

		return $this;
	}
}
