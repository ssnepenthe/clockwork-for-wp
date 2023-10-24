<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use Closure;

final class Route_Handler_Invoker {
	private $param_prefix;
	private $param_resolver;
	private $callable_resolver;

	public function __construct(
		string $param_prefix = '',
		?Closure $param_resolver = null,
		?Closure $callable_resolver = null
	) {
		$this->param_prefix = $param_prefix;

		$this->param_resolver = $param_resolver
			? $param_resolver->bindTo( $this )
			: static function () {
				return [];
			};

		$this->callable_resolver = $callable_resolver ?: static fn( $callable ) => $callable;
	}

	public function invoke_handler( Route $route ) {
		// @todo Allow for default qv values?
		// @todo Type juggling on qv values as well? e.g. "1" to true.
		return call_user_func(
			( $this->callable_resolver )( $route->get_handler() ),
			$this->get_additional_params( $route )
		);
	}

	public function strip_param_prefix( $param_name ) {
		$prefix_length = \mb_strlen( $this->param_prefix );

		if ( \mb_substr( $param_name, 0, $prefix_length ) === $this->param_prefix ) {
			return \substr_replace( $param_name, '', 0, $prefix_length );
		}

		return $param_name;
	}

	private function get_additional_params( $route ) {
		return ( $this->param_resolver )( $route );
	}
}
