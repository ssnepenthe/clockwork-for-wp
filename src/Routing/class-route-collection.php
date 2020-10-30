<?php

namespace Clockwork_For_Wp\Routing;

class Route_Collection {
	protected $routes = [];

	public function __construct( Route ...$routes ) {
		$this->routes = $routes;
	}

	public function add( Route $route ) {
		$this->routes[ $route->get_method() . ':' . $route->get_regex() ] = $route;

		return $this;
	}

	public function get( string $regex, string $query, $handler ) {
		return $this->add( new Route( 'GET', $regex, $query, $handler ) );
	}

	public function post( string $regex, string $query, $handler ) {
		return $this->add( new Route( 'POST', $regex, $query, $handler ) );
	}

	// @todo Method name.
	public function match( $method, $matched_pattern ) {
		$key = $method . ':' . $matched_pattern;

		if ( ! array_key_exists( $key, $this->routes ) ) {
			return null;
		}

		return $this->routes[ $key ];
	}

	public function get_rewrite_array() {
		$rewrite_array = [];

		foreach ( $this->routes as $route ) {
			$rewrite_array[ $route->get_regex() ] = $route->get_query();
		}

		return $rewrite_array;
	}

	public function get_query_vars() {
		$query_vars = [];

		foreach ( $this->routes as $route ) {
			$query_vars = array_merge( $query_vars, $route->get_query_array() );
		}

		return array_keys( $query_vars );
	}
}
