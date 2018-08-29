<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\DataSource\DataSource;

class Core extends DataSource {
	/**
	 * @param  Request $request
	 * @return Request
	 */
	public function resolve( Request $request ) {
		// @todo Consider options for filling the "controller" slot.

		$panel = $request->userData( 'WordPress' );

		$panel->counters( [
			'WP Version' => get_bloginfo( 'version' ),
		] );

		$request->timelineData = array_merge(
			$request->timelineData,
			$this->build_timeline( $request )
		);

		return $request;
	}

	protected function build_timeline( Request $request ) {
		$timeline = new Timeline();

		$timeline->startEvent( 'total', 'Total execution', 'start' );

		return $timeline->finalize( $request->time );
	}
}
