<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\DataSource\DataSource;

class Timestart extends DataSource {
	protected $timestart;

	public function __construct( $timestart = null ) {
		$this->timestart = $timestart;
	}

	public function resolve( Request $request ) {
		$request->timelineData = array_merge(
			$request->timelineData,
			$this->build_timeline( $request )
		);

		return $request;
	}

	public function set_timestart( $timestart ) {
		$this->timestart = is_float( $timestart ) ? $timestart : null;
	}

	protected function build_timeline( Request $request ) {
		if ( null === $this->timestart ) {
			return [];
		}

		$timeline = new Timeline();

		$timeline->addEvent( 'core_timer', 'Core timer start', $this->timestart, $this->timestart );

		return $timeline->finalize();
	}
}
