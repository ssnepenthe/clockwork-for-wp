<?php

namespace Clockwork_For_Wp\Routing;

use Closure;
use Invoker\Invoker;

class Route_Handler_Invoker {
	protected $invoker;
	protected $param_prefix;
	protected $param_resolver;

	public function __construct(
		Invoker $invoker,
		string $param_prefix = '',
		Closure $param_resolver = null
	) {
		$this->invoker = $invoker;
		$this->param_prefix = $param_prefix;

		$this->param_resolver = $param_resolver
			? $param_resolver->bindTo( $this )
			: static function () { return []; };
	}

	public function invoke_handler( Route $route ) {
		// @todo Allow for default qv values?
		// @todo Type juggling on qv values as well? e.g. "1" to true.
		return $this->invoker->call(
			$route->get_handler(),
			$this->get_additional_params( $route )
		);
	}

	public function strip_param_prefix( $param_name ) {
		$prefix_length = strlen( $this->param_prefix );

		if ( substr( $param_name, 0, $prefix_length ) === $this->param_prefix ) {
			return substr_replace( $param_name, '', 0, $prefix_length );
		}

		return $param_name;
	}

	protected function get_additional_params( $route ) {
		return call_user_func( $this->param_resolver, $route );
	}
}
