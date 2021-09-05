<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

final class Route_Collection {
	private $prefix;
	private $routes = [];

	public function __construct( string $prefix = '' ) {
		$this->prefix = $prefix;
	}

	public function add( Route $route ) {
		$route->set_prefix( $this->prefix );

		$this->routes[ $route->get_method() . ':' . $route->get_regex() ] = $route;

		return $this;
	}

	public function get( string $regex, string $query, $handler ) {
		return $this->add( new Route( 'GET', $regex, $query, $handler ) );
	}

	public function get_query_vars() {
		$query_vars = [];

		foreach ( $this->routes as $route ) {
			$query_vars = \array_merge( $query_vars, $route->get_query_array() );
		}

		return \array_keys( $query_vars );
	}

	public function get_rewrite_array() {
		$rewrite_array = [];

		foreach ( $this->routes as $route ) {
			$rewrite_array[ $route->get_regex() ] = $route->get_query();
		}

		return $rewrite_array;
	}

	public function get_rewrite_array_for_method( string $method ) {
		$rewrite_array = [];

		foreach ( $this->routes as $route ) {
			if ( $method !== $route->get_method() ) {
				continue;
			}

			$rewrite_array[ $route->get_regex() ] = $route->get_query();
		}

		return $rewrite_array;
	}

	// @todo Method name.
	public function match( $method, $matched_pattern ) {
		$key = $method . ':' . $matched_pattern;

		if ( ! \array_key_exists( $key, $this->routes ) ) {
			return;
		}

		return $this->routes[ $key ];
	}

	public function post( string $regex, string $query, $handler ) {
		return $this->add( new Route( 'POST', $regex, $query, $handler ) );
	}

	public function put( string $regex, string $query, $handler ) {
		return $this->add( new Route( 'PUT', $regex, $query, $handler ) );
	}
}
