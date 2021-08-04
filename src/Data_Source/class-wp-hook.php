<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Subscriber;

use function Clockwork_For_Wp\describe_callable;

class Wp_Hook extends DataSource implements Subscriber {
	protected $hooks = [];
	protected $except_tags;
	protected $only_tags;
	protected $except_callbacks;
	protected $only_callbacks;

	public function __construct(
		array $except_tags,
		array $only_tags,
		array $except_callbacks,
		array $only_callbacks
	) {
		$this->except_tags = $except_tags;
		$this->only_tags = $only_tags;
		$this->except_callbacks = $except_callbacks;
		$this->only_callbacks = $only_callbacks;
	}

	public function get_subscribed_events() : array {
		return [
			'cfw_pre_resolve' => function( $wp_filter, $wp_actions ) {
				// @todo whitelist/blacklist for hooks to ignore?
				foreach ( array_keys( $wp_actions ) as $tag ) {
					if ( isset( $wp_filter[ $tag ] ) ) {
						foreach ( $wp_filter[ $tag ] as $priority => $callbacks ) {
							foreach ( $callbacks as $callback ) {
								$this->add_hook(
									$tag,
									$priority,
									$callback['function'],
									$callback['accepted_args']
								);
							}
						}
					} else {
						$this->add_hook( $tag );
					}
				}
			},
		];
	}

	public function resolve( Request $request ) {
		if ( count( $this->hooks ) > 0 ) {
			$request->userData( 'Hooks' )->table( 'Hooks', $this->hooks );
		}

		return $request;
	}

	public function add_hook(
		string $tag,
		int $priority = null,
		callable $callback = null,
		int $accepted_args = null
	) {
		if ( ! $this->should_collect( 'tag', $tag ) ) {
			return;
		}

		$callback = null !== $callback ? describe_callable( $callback ) : '';

		if ( ! $this->should_collect( 'callback', $callback ) ) {
			return;
		}

		// @todo Should empty values be filtered out?
		// @todo $this->applyFilters()?
		$this->hooks[] = [
			'Tag' => $tag,
			'Priority' => null !== $priority ? (string) $priority : '',
			'Callback' => $callback,
			'Accepted Args' => null !== $accepted_args ? (string) $accepted_args : '',
		];
	}

	protected function should_collect( $type, $value ) {
		if ( ! in_array( $type, [ 'callback', 'tag' ], true ) ) {
			throw new \InvalidArgumentException( '@todo' );
		}

		$only = "only_{$type}s";
		$except = "except_{$type}s";

		if ( count( $this->{$only} ) > 0 ) {
			$pattern = implode( '|', $this->{$only} );

			return 1 === preg_match( "/{$pattern}/", $value );
		}

		if ( count( $this->{$except} ) > 0 ) {
			$pattern = implode( '|', $this->{$except} );

			return 1 !== preg_match( "/{$pattern}/", $value );
		}

		return true;
	}
}
