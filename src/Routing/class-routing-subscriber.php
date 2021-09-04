<?php

namespace Clockwork_For_Wp\Routing;

use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Incoming_Request;
use WP;

class Routing_Subscriber implements Subscriber {
	public function call_matched_handler(
		Route_Collection $routes,
		WP $wp,
		Route_Handler_Invoker $invoker,
		Incoming_Request $request
	) {
		// @todo $route = $routes->match( $request );?
		$route = $routes->match( $request->intended_method(), $wp->matched_rule );

		if ( null === $route ) {
			return;
		}

		$invoker->invoke_handler( $route );
	}

	public function diff_rules( $rules, Route_Collection $routes ) {
		if ( ! $this->should_modify_rules( $rules ) ) {
			return $rules;
		}

		return array_diff_key( $rules, $routes->get_rewrite_array() );
	}

	// @todo route collection via constructor?
	public function get_subscribed_events(): array {
		return [
			'option_rewrite_rules' => 'merge_rules',
			'rewrite_rules_array' => 'merge_rules',
			'pre_update_option_rewrite_rules' => 'diff_rules',
			'query_vars' => 'merge_query_vars',

			// 'parse_query' => 'reset_query_flags',
			'template_redirect' => 'call_matched_handler',
		];
	}

	public function merge_query_vars( $query_vars, Route_Collection $routes ) {
		return array_merge( $routes->get_query_vars(), $query_vars );
	}

	public function merge_rules( $rules, Route_Collection $routes, Incoming_Request $request ) {
		if ( ! $this->should_modify_rules( $rules ) ) {
			return $rules;
		}

		return array_merge(
			$routes->get_rewrite_array_for_method( $request->intended_method() ),
			$rules
		);
	}

	public function reset_query_flags() {
		// @todo
	}

	protected function should_modify_rules( $rules ) {
		return is_array( $rules ) && count( $rules ) > 0;
	}
}
