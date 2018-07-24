<?php

namespace Clockwork_For_Wp;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp_Hook_Data_Source extends DataSource {
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
					$event['listeners'][] = $this->format_callback(
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
	protected function format_callback( $callback, $priority ) {
		if ( is_string( $callback ) ) {
			return "{$callback}@{$priority}";
		}

		if ( is_array( $callback ) && 2 === count( $callback ) ) {
			if ( is_object( $callback[0] ) ) {
				return get_class( $callback[0] ) . "->{$callback[1]}@{$priority}";
			} else {
				return "{$callback[0]}::{$callback[1]}@{$priority}";
			}
		}

		if ( $callback instanceof \Closure ) {
			return "Closure@{$priority}";
		}

		return "(Unknown)";
	}
}
