<?php

namespace Clockwork_For_Wp;

use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\Helpers\Serializer;
use Clockwork\DataSource\DataSource;

// @todo Inject globals.
class Wp_Data_Source extends DataSource {
	/**
	 * @var Timeline
	 */
	protected $emails;

	/**
	 * @var Timeline
	 */
	protected $timeline;

	/**
	 * @param Timeline|null $emails
	 * @param Timeline|null $timeline
	 */
	public function __construct( Timeline $emails = null, Timeline $timeline = null ) {
		$this->emails = $emails ?: new Timeline();
		$this->timeline = $timeline ?: new Timeline();
	}

	/**
	 * @param  Request $request
	 * @return Request
	 */
	public function resolve( Request $request ) {
		// @todo Consider options for filling the "controller" slot.
		// @todo Consider configuring a custom error handler to save errors in the "log" slot.
		$request->cacheHits = $this->collect_cache_hits();
		$request->cacheReads = $this->collect_cache_reads();
		$request->databaseQueries = $this->collect_database_queries();
		$request->emailsData = $this->emails->finalize();
		$request->events = $this->collect_events();
		$request->routes = $this->collect_routes();
		$request->timelineData = $this->timeline->finalize( $request->time );
		$request->viewsData = $this->collect_views_data();

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

		$this->timeline->addEvent( 'core_timer', 'Core timer start', $timestart, $timestart );

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

		add_action(
			'wp_mail',
			/**
			 * @param array<string, mixed>  $args
			 * @return array<string, mixed>
			 */
			function( $args ) {
				// @todo This only captures ATTEMPTS to send email...
				$this->emails->addEvent(
					'email_' . hash( 'md5', serialize( $args ) ),
					'Sending an email',
					null,
					null,
					$args
				);

				return $args;
			}
		);
	}

	/**
	 * @return integer
	 */
	protected function collect_cache_reads() {
		global $wp_object_cache;

		return $wp_object_cache->cache_hits + $wp_object_cache->cache_misses;
	}

	/**
	 * @return integer
	 */
	protected function collect_cache_hits() {
		global $wp_object_cache;

		return $wp_object_cache->cache_hits;
	}

	/**
	 * @return array<int, array>
	 */
	protected function collect_database_queries() {
		global $wpdb;

		return array_map(
			/**
			 * @psalm-param array{0: string, 1: float, 2: string}  $query
			 * @psalm-return array{query: string, duration: float}
			 */
			function( $query ) {
				list( $query, $duration, $caller ) = $query;

				return [
					// @todo Consider highlighting keywords in query.
					'query' => $query,
					// @todo Verify multiplier - wpdb uses microtime which returns seconds - we want milliseconds so this should be correct.
					'duration' => $duration * 1000,
					// @todo Consider populating this value based on queried table?
					// 'model' => '',
					// @todo Probably not able to record this data without configuring a db dropin.
					// 'file' => '',
					// 'line' => '',
				];
			},
			$wpdb->queries
		);
	}

	/**
	 * @return array<int, array>
	 */
	protected function collect_events() {
		global $wp_actions, $wp_filter;

		$events = [];

		foreach ( array_keys( $wp_actions ) as $tag ) {
			if ( ! isset( $wp_filter[ $tag ] ) ) {
				continue;
			}

			$event = [
				'data' => '',
				'event' => $tag,
				'listeners' => [],
				// 'time' => microtime( true ),
				// 'file' => '@todo',
				// 'line' => '@todo',
			];

			foreach ( $wp_filter[ $tag ] as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					// @todo Not enough detail...
					$event['listeners'][] = $this->format_event_callback(
						$callback['function'],
						$priority
					);
				}
			}

			$events[] = $event;
		}

		return $events;
	}

	/**
	 * @param  callable $callback
	 * @param  integer  $priority
	 * @return string
	 */
	protected function format_event_callback( $callback, $priority ) {
		if ( is_string( $callback ) ) {
			return "{$callback}@{$priority}";
		}

		if ( is_array( $callback ) && 2 === count( $callback ) ) {
			if ( is_object( $callback[0] ) ) {
				return get_class( $callback[0] ) . "->{$callback[1]}@{$priority}";
			} else {
				return "{$callback[0]}:{$callback[1]}@{$priority}";
			}
		}

		if ( $callback instanceof \Closure ) {
			return "Closure@{$priority}";
		}

		return "(Unknown)";
	}

	/**
	 * @return array<string, array>
	 */
	protected function collect_views_data() {
		$template = get_template_directory();
		$stylesheet = get_stylesheet_directory();
		$includes = get_included_files();

		// @todo This will list all included theme files. I think it would be a drastic improvement to only show the main template file (the file included @ template_include) and included template parts (files for which a get_template_part_{$slug} action has been triggered).
		$templates = array_filter(
			array_combine(
				$includes,
				array_map(
					/**
					 * @param string  $file
					 * @return string
					 */
					function( $file ) use ( $template, $stylesheet ) {
						return str_replace( [ $template, $stylesheet ], '', $file );
					},
					$includes
				)
			),
			/**
			 * @param  string $absolute
			 * @param  string $relative
			 * @return boolean
			 */
			function( $absolute, $relative ) {
				return $absolute !== $relative;
			},
			ARRAY_FILTER_USE_BOTH
		);

		$views = new Timeline();

		foreach ( $templates as $absolute => $relative ) {
			$views->addEvent( "view_{$absolute}", 'Rendering a view', null, null, [
				'name' => $relative,
				'data' => '(unknown)', // @todo Can we retrieve data used to render view? Probably not since it comes from globals or global function calls...
			] );
		}

		return $views->finalize();
	}

	/**
	 * @return array<int, array>
	 */
	protected function collect_routes() {
		global $wp_rewrite;

		// @todo What does wp_rewrite_rules() return when pretty permalinks are disabled?
		$rules = $wp_rewrite->wp_rewrite_rules();

		// @todo Routes may not be the most appropriate place to put all of the WordPress rewrites...
		return array_map(
			/**
			 * @param        string $regex
			 * @param        string $query
			 * @psalm-return array{uri: string, action: string}
			 */
			function( $regex, $query ) {
				return [
					'uri' => $regex,
					'action' => $query,
				];
			},
			$rules,
			array_keys( $rules )
		);
	}
}
