<?php

namespace Clockwork_For_Wp\Routing;

use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Routing\Route_Collection;
use WP;

class Routing_Subscriber implements Subscriber {
	// @todo route collection via constructor?
	public function get_subscribed_events() : array {
		return [
			'option_rewrite_rules' => 'merge_rules',
			'rewrite_rules_array' => 'merge_rules',
			'pre_update_option_rewrite_rules' => 'diff_rules',
			'query_vars' => 'merge_query_vars',

			// 'parse_query' => 'reset_query_flags',
			'template_redirect' => 'call_matched_handler',
		];
	}

	public function merge_rules( $rules, Route_Collection $routes ) {
		if ( ! $this->should_modify_rules( $rules ) ) {
			return $rules;
		}

		return array_merge( $routes->get_rewrite_array(), $rules );
	}

	public function diff_rules( $rules, Route_Collection $routes ) {
		if ( ! $this->should_modify_rules( $rules ) ) {
			return $rules;
		}

		return array_diff_key( $rules, $routes->get_rewrite_array() );
	}

	public function merge_query_vars( $query_vars, Route_Collection $routes ) {
		return array_merge( $routes->get_query_vars(), $query_vars );
	}

	public function reset_query_flags() {
		// @todo
	}

	public function call_matched_handler(
		Route_Collection $routes,
		WP $wp,
		Route_Handler_Invoker $invoker
	) {
		$route = $routes->match( $_SERVER['REQUEST_METHOD'], $wp->matched_rule );

		if ( null === $route ) {
			return;
		}

		$invoker->invoke_handler( $route );
	}

	protected function should_modify_rules( $rules ) {
		return is_array( $rules ) && count( $rules ) > 0;
	}
}
