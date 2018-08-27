<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

/**
 * Copy of the Xdebug datasource bundled with Clockwork except that the profiler filename is
 * resolved on the WordPress init hook.
 *
 * It appears that shutdown functions can cause secondary profiler files to be created. Since we are
 * resolving Clockwork requests in a shutdown function, the bundled xdebug datasource appears to
 * end up grabbing profiling data for the previously executed shutdown function.
 *
 * @todo Need to look into this more - is it a bug or intended behavior?
 */
class Xdebug extends DataSource {
	protected $profiler_filename;

	public function resolve( Request $request ) {
		$request->xdebug = [
			'profile' => $this->profiler_filename,
		];

		return $request;
	}

	public function extend( Request $request ) {
		$profile = isset( $request->xdebug['profile'] ) ? $request->xdebug['profile'] : null;

		if ( $profile && ! preg_match( '/\.php$/', $profile ) && is_readable( $profile ) ) {
			$request->xdebug['profileData'] = file_get_contents( $profile );
		}

		return $request;
	}

	public function on_init() {
		$this->profiler_filename = xdebug_get_profiler_filename();
	}
}
