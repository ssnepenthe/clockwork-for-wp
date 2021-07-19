<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline\Timeline;

class Core extends DataSource {
	protected $version;
	protected $timestart;

	public function __construct( $version, $timestart ) {
		$this->version = $version;
		$this->timestart = $timestart;
	}

	public function set_version( $version ) {
		$this->version = $version;

		return $this;
	}

	public function set_timestart( $timestart ) {
		$this->timestart = $timestart;

		return $this;
	}

	public function resolve( Request $request ) {
		// @todo Consider options for filling the "controller" slot.
		$request->userData( 'WordPress' )->counters( [
			'WP Version' => $this->version,
		] );

		$request->timeline()->merge( $this->build_timeline() );

		return $request;
	}

	protected function build_timeline() {
		$timeline = new Timeline();

		$timeline->event( 'Total Execution', [
			'name' => 'total',
		] );
		$timeline->event( 'Core Timer Start', [
			'name' => 'core_timer',
			'start' => $this->timestart,
			'end' => $this->timestart,
		] );

		return $timeline;
	}
}
