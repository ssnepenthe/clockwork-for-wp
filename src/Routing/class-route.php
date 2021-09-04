<?php

namespace Clockwork_For_Wp\Routing;

class Route {
	protected $handler;
	protected $method;
	protected $prefix = '';
	protected $query;
	protected $regex;

	public function __construct( string $method, string $regex, string $query, $handler ) {
		$this->method = $method;
		$this->regex = $regex;
		$this->query = $query;
		$this->handler = $handler;
	}

	public function get_handler() {
		return $this->handler;
	}

	public function get_method() {
		return $this->method;
	}

	public function get_query() {
		$query_array = $this->get_query_array();

		$query_string = implode( '&', array_map( function ( $key, $value ) {
			return "{$key}={$value}";
		}, array_keys( $query_array ), $query_array ) );

		return "index.php?{$query_string}";
	}

	public function get_query_array() {
		$prefixed = [];

		foreach ( $this->get_raw_query_array() as $key => $value ) {
			$prefixed[ "{$this->prefix}{$key}" ] = $value;
		}

		return $prefixed;
	}

	public function get_query_vars() {
		return array_keys( $this->get_query_array() );
	}

	public function get_raw_query() {
		return $this->query;
	}

	public function get_raw_query_array() {
		$query_string = parse_url( $this->query, PHP_URL_QUERY );

		parse_str( $query_string, $query_array );

		return $query_array;
	}

	public function get_raw_query_vars() {
		return array_keys( $this->get_raw_query_array() );
	}

	public function get_regex() {
		return $this->regex;
	}

	public function set_prefix( string $prefix ) {
		$this->prefix = $prefix;
	}
}
