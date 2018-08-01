<?php

namespace Clockwork_For_Wp;

use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\Helpers\Serializer;
use Clockwork\DataSource\DataSource;

// @todo Inject globals.
class Wp_Data_Source extends DataSource {
	protected $log;

	/**
	 * @var Timeline
	 */
	protected $timeline;

	/**
	 * @param Timeline|null $timeline
	 */
	public function __construct( Log $log = null, Timeline $timeline = null ) {
		$this->log = $log ?: new Log();
		$this->timeline = $timeline ?: new Timeline();
	}

	/**
	 * @param  Request $request
	 * @return Request
	 */
	public function resolve( Request $request ) {
		// @todo Consider options for filling the "controller" slot.
		// @todo Consider configuring a custom error handler to save errors in the "log" slot.
		$request->log = array_merge( $request->log, $this->log->toArray() );
		$request->timelineData = array_merge(
			$request->timelineData,
			$this->timeline->finalize( $request->time )
		);

		return $request;
	}

	/**
	 * @return void
	 */
	public function listen_to_events() {
		global $timestart;

		$current_time = microtime( true );

		$this->timeline->startEvent( 'total', 'Total execution', 'start' );

		$this->timeline->addEvent(
			'initialization',
			'WP initialization',
			'start',
			$current_time
		);

		$this->timeline->addEvent(
			'plugins_loaded',
			'Plugins loaded',
			$current_time,
			$current_time
		);

		add_action(
			'setup_theme',
			/**
			 * @return void
			 */
			function() {
				$this->timeline->startEvent( 'theme_initialization', 'Theme initialization' );
			},
			Plugin::EARLY_EVENT
		);

		add_action(
			'after_setup_theme',
			/**
			 * @return void
			 */
			function() {
				$this->timeline->endEvent( 'theme_initialization' );
			},
			Plugin::LATE_EVENT
		);

		add_action(
			'init',
			/**
			 * @return void
			 */
			function() {
				// @todo Can this be divided into more specific segments?
				$this->timeline->startEvent( 'pre_render', 'Pre render' );
			},
			Plugin::EARLY_EVENT
		);

		add_action(
			'template_redirect',
			/**
			 * @return void
			 */
			function() {
				$this->timeline->endEvent( 'pre_render' );
			},
			Plugin::LATE_EVENT
		);

		add_action(
			'template_include',
			/**
			 * @param string  $template
			 * @return string
			 */
			function( $template ) {
				$this->timeline->startEvent( 'render', 'Render' );

				return $template;
			},
			Plugin::EARLY_EVENT
		);

		add_action(
			'wp_footer',
			/**
			 * @return void
			 */
			function() {
				$this->timeline->endEvent( 'render' );
			},
			Plugin::LATE_EVENT
		);

		$this->timeline->addEvent( 'core_timer', 'Core timer start', $timestart, $timestart );

		// @todo Not sure if this is actually a good idea...
		$this->hijack_doing_it_wrong();
	}

	protected function hijack_doing_it_wrong() {
		add_filter( 'doing_it_wrong_trigger_error', '__return_false' );

		add_action( 'doing_it_wrong_run', function( $function, $message, $version ) {
			// @todo Translations!
			$context = [
				'link' => 'https://codex.wordpress.org/Debugging_in_WordPress',
			];

			if ( is_string( $version ) ) {
				$context['version'] = $version;
			}

			if ( is_string( $message ) ) {
				$context['message'] = $message;
			}

			// @todo What is appropriate level here? Core triggers an error.
			$this->log->warning( "_doing_it_wrong: {$function} was called incorrectly", $context );
		}, 10, 3 );
	}
}
