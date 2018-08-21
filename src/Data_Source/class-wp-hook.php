<?php

namespace Clockwork_For_Wp\Data_Source;

use Closure;
use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp_Hook extends DataSource {
	protected $wp_actions_resolver;
	protected $wp_filter_resolver;

	public function __construct() {
		// @todo Would it be better/cleaner/easier to pass globals to the constructor by reference?
		$this->wp_actions_resolver = function() {
			if ( isset( $GLOBALS['wp_actions'] ) && is_array( $GLOBALS['wp_actions'] ) ) {
				return $GLOBALS['wp_actions'];
			}

			return [];
		};

		$this->wp_filter_resolver = function() {
			if ( isset( $GLOBALS['wp_filter'] ) && is_array( $GLOBALS['wp_filter'] ) ) {
				return $GLOBALS['wp_filter'];
			}

			return [];
		};
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'hooks' )->title( 'Hooks' );
		$panel->table( 'Hooks', $this->hooks_table() );

		return $request;
	}

	public function get_wp_actions() {
		return call_user_func( $this->wp_actions_resolver );
	}

	public function get_wp_filter() {
		return call_user_func( $this->wp_filter_resolver );
	}

	public function resolve_wp_actions_using( Closure $resolver ) {
		$this->wp_actions_resolver = $resolver;
	}

	public function resolve_wp_filter_using( Closure $resolver ) {
		$this->wp_filter_resolver = $resolver;
	}

	protected function hooks_table() {
		$all = $this->get_wp_filter();
		$table = [];

		foreach ( array_keys( $this->get_wp_actions() ) as $tag ) {
			if ( isset( $all[ $tag ] ) ) {
				foreach ( $all[ $tag ] as $priority => $callbacks ) {
					foreach ( $callbacks as $callback ) {
						$table[] = $this->hooks_row(
							$tag,
							$priority,
							$callback['function'],
							$callback['accepted_args']
						);
					}
				}
			} else {
				$table[] = $this->hooks_row( $tag );
			}
		}

		return $table;
	}

	protected function hooks_row( $tag, $priority = null, $cb = null, $args = null ) {
		return [
			'Tag' => (string) $tag,
			'Priority' => null !== $priority ? (string) $priority : '',
			'Callback' => null !== $cb ? \Clockwork_For_Wp\callable_to_display_string( $cb ) : '',
			'Accepted Args' => null !== $args ? (string) $args : '',
		];
	}
}
