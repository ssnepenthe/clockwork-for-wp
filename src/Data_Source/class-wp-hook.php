<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp_Hook extends DataSource {
	public function resolve( Request $request ) {
		$request->events = $this->collect_events();

		return $request;
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
					$event['listeners'][] = \Clockwork_For_Wp\callable_to_display_string(
						$callback['function']
					) . " (priority {$priority})";
				}
			}

			$events[] = $event;
		}

		return $events;
	}
}
