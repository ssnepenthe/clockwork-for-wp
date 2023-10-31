<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Incoming_Request;
use WP;

use function Clockwork_For_Wp\service;

final class Routing_Subscriber implements Subscriber {
	private $invoker;

	private $request;

	private $routes;

	public function __construct( Route_Collection $routes, Route_Handler_Invoker $invoker, Incoming_Request $request ) {
		$this->routes = $routes;
		$this->invoker = $invoker;
		$this->request = $request;
	}

	public function call_matched_handler(): void {
		// @todo $route = $this->routes->match( $request );?
		$route = $this->routes->match( $this->request->intended_method(), service( WP::class )->matched_rule );

		if ( null === $route ) {
			return;
	}

		$this->invoker->invoke_handler( $route );
	}

	public function diff_rules( $rules ) {
		if ( ! $this->should_modify_rules( $rules ) ) {
			return $rules;
	}

		return \array_diff_key( $rules, $this->routes->get_rewrite_array() );
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

	public function merge_query_vars( $query_vars ) {
		return \array_merge( $this->routes->get_query_vars(), $query_vars );
	}

	public function merge_rules( $rules ) {
		if ( ! $this->should_modify_rules( $rules ) ) {
			return $rules;
		}

		return \array_merge(
			$this->routes->get_rewrite_array_for_method( $this->request->intended_method() ),
			$rules
		);
	}

	public function reset_query_flags(): void {
		// @todo
	}

	private function should_modify_rules( $rules ) {
		return \is_array( $rules ) && \count( $rules ) > 0;
	}
}
