<?php

namespace Clockwork_For_Wp\Routing;

class Route {
	protected $method;
	protected $regex;
	protected $query;
	protected $handler;

	public function __construct( string $method, string $regex, string $query, $handler ) {
		$this->method = $method;
		$this->regex = $regex;
		$this->query = $query;
		$this->handler = $handler;
	}

	public function get_method() {
		return $this->method;
	}

	public function get_regex() {
		return $this->regex;
	}

	public function get_query() {
		return $this->query;
	}

	public function get_handler() {
		return $this->handler;
	}

	public function get_query_array() {
		$query_string = parse_url( $this->query, PHP_URL_QUERY );

		parse_str( $query_string, $query_array );

		return $query_array;
	}

	public function get_query_vars() {
		return array_keys( $this->get_query_array() );
	}
}
