<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\XdebugDataSource;
use Clockwork\Request\Request;
use ToyWpEventManagement\SubscriberInterface;

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
final class Xdebug extends XdebugDataSource implements SubscriberInterface {
	private $profiler_filename;

	public function onInit(): void {
		$filename = \xdebug_get_profiler_filename();

		/**
		 * @psalm-suppress RedundantCondition
		 *
		 * @see https://github.com/vimeo/psalm/issues/6484
		 */
		if ( \is_string( $filename ) ) {
			$this->set_profiler_filename( $filename );
		}
	}

	public function getSubscribedEvents(): array
	{
		return [
			'init' => 'onInit',
		];
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
