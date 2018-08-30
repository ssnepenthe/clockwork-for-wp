<?php

namespace Clockwork_For_Wp;

class Route_Manager {
	protected $cached_rules;
	protected $cached_query_vars;
	protected $routes = [];
	protected $wp;

	public function __construct( $wp = null ) {
		$this->set_wp( $wp );
	}

	public function add( Route $route ) {
		$this->routes[ $route->get_regex() ] = $route;
	}

	/**
	 * @hook template_redirect
	 */
	public function call_matched_handler() {
		$handler = $this->get_matched_handler();

		if ( null === $handler ) {
			return;
		}

		call_user_func( $handler );
	}

	public function get_matched_handler() {
		if (
			null === $this->wp
			|| ! property_exists( $this->wp, 'matched_rule' )
			|| ! $this->wp->matched_rule
		) {
			return;
		}

		if ( ! array_key_exists( $this->wp->matched_rule, $this->routes ) ) {
			return;
		}

		return $this->routes[ $this->wp->matched_rule ]->get_handler_for_method(
			$_SERVER['REQUEST_METHOD']
		);
	}

	public function get_rewrite_rules_array() {
		if ( count( $this->routes ) < 1 ) {
			return [];
		}

		if ( is_array( $this->cached_rules ) ) {
			return $this->cached_rules;
		}

		$this->cached_rules = call_user_func_array(
			'array_merge',
			array_map( function( $route ) {
				return [ $route->get_regex() => $route->get_query() ];
			}, $this->routes )
		);

		return $this->cached_rules;
	}

	public function get_unique_query_vars() {
		if ( count( $this->routes ) < 1 ) {
			return [];
		}

		if ( is_array( $this->cached_query_vars ) ) {
			return $this->cached_query_vars;
		}

		$query_vars = array_filter( array_map( function( $route ) {
			$vars = $route->get_query_vars();

			return is_array( $vars ) && count( $vars ) > 0 ? $vars : null;
		}, $this->routes ) );

		if ( count( $query_vars ) > 0 ) {
			$query_vars = call_user_func_array( 'array_merge', $query_vars );
		}

		$this->cached_query_vars = $query_vars;

		return $this->cached_query_vars;
	}

	public function get_wp() {
		return $this->wp;
	}

	/**
	 * @hook pre_update_option_rewrite_rules
	 */
	public function diff_rewrite_rules( $value ) {
		if ( count( $this->routes ) < 1 || ! is_array( $value ) || count( $value ) < 1 ) {
			return $value;
		}

		return array_diff_key( $value, $this->get_rewrite_rules_array() );
	}

	/**
	 * @hook query_vars
	 */
	public function merge_query_vars( $query_vars ) {
		return array_merge( $this->get_unique_query_vars(), $query_vars );
	}

	/**
	 * @hook option_rewrite_rules
	 * @hook rewrite_rules_array
	 */
	public function merge_rewrite_rules( $value ) {
		if ( count( $this->routes ) < 1 || ! is_array( $value ) || count( $value ) < 1 ) {
			return $value;
		}

		return array_merge( $this->get_rewrite_rules_array(), $value );
	}

	public function reset_query_flags( $query ) {
		if ( null === $this->get_matched_handler() ) {
			return;
		}

		// @todo Should we use an explicit list here instead? Probably...
		$flags = array_filter( array_keys( get_object_vars( $query ) ), function( $var ) {
			return 'is_' === substr( $var, 0, 3 );
		} );

		foreach ( $flags as $flag ) {
			$query->{$flag} = false;
		}
	}

	public function set_wp( $wp ) {
		$this->wp = is_object( $wp ) ? $wp : null;
	}
}
