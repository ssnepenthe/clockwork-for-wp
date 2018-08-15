<?php

namespace Clockwork_For_Wp;

class Route {
	protected $handlers;
	protected $query;
	protected $query_vars;
	protected $regex;

	public function __construct( $regex, $query ) {
		$this->set_query( $query );
		$this->set_regex( $regex );
	}

	public function get_handler_for_method( $method ) {
		$method = strtoupper( (string) $method );

		if ( ! array_key_exists( $method, $this->handlers ) ) {
			return null;
		}

		return $this->handlers[ $method ];
	}

	public function get_query() {
		return $this->query;
	}

	public function get_query_vars() {
		return $this->query_vars;
	}

	public function get_regex() {
		return $this->regex;
	}

	public function map( $method, callable $handler ) {
		$method = strtoupper( (string) $method );

		$this->handlers[ $method ] = $handler;
	}

	public function set_query( $query ) {
		$this->query = (string) $query;
	}

	public function set_query_vars( $query_vars ) {
		$this->query_vars = array_values( array_map(
			'strval',
			is_array( $query_vars ) ? $query_vars : [ $query_vars ]
		) );
	}

	public function set_regex( $regex ) {
		$this->regex = (string) $regex;
	}
}
