<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

final class Route_Collection {
	private $converter;

	private $routes = [];

	public function __construct( Fastroute_Converter $converter ) {
		$this->converter = $converter;
	}

	public function add( Route $route ): self {
		$this->routes[ $route->get_method() . ':' . $route->get_regex() ] = $route;

		return $this;
	}

	public function get( string $route, $handler ): self {
		return $this->convert_and_map( 'GET', $route, $handler );
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

	public function match( $method, $matched_pattern ) {
		$key = $method . ':' . $matched_pattern;

		if ( ! \array_key_exists( $key, $this->routes ) ) {
			return;
		}

		return $this->routes[ $key ];
	}

	public function post( string $route, $handler ) {
		return $this->convert_and_map( 'POST', $route, $handler );
	}

	public function put( string $route, $handler ) {
		return $this->convert_and_map( 'PUT', $route, $handler );
	}

	private function convert_and_map( string $method, string $route, $handler ): self {
		$rewrites = $this->converter->convert( $route );

		foreach ( $rewrites as $regex => $query ) {
			$this->add( new Route( $method, $regex, $query, $handler ) );
		}

		return $this;
	}
}
