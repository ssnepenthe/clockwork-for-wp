<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

final class Route {
	private $handler;

	private $method;

	private $query;

	private $query_array;

	private $query_vars;

	private $regex;

	public function __construct( string $method, string $regex, array $query, $handler ) {
		$this->method = $method;
		$this->regex = $regex;
		$this->query = 'index.php?' . build_unencoded_query( $query );
		$this->query_array = $query;
		$this->query_vars = \array_keys( $query );
		$this->handler = $handler;
	}

	public function get_handler() {
		return $this->handler;
	}

	public function get_method() {
		return $this->method;
	}

	public function get_query() {
		return $this->query;
	}

	public function get_query_array() {
		return $this->query_array;
	}

	public function get_query_vars() {
		return $this->query_vars;
	}

	public function get_regex() {
		return $this->regex;
	}
}
