<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Closure;

use function Clockwork_For_Wp\describe_callable;
use function Clockwork_For_Wp\describe_unavailable_callable;

class Wp_Hook extends DataSource implements Subscriber {
	protected $hooks = [];
	protected $all_hooks;

	public function __construct( bool $all_hooks = false ) {
		$this->all_hooks = $all_hooks;
	}

	public function get_subscribed_events() : array {
		return [
			'cfw_pre_resolve' => function( $wp_filter, $wp_actions ) {
				$tags = $this->all_hooks ? array_keys( $wp_filter ) : array_keys( $wp_actions );

				foreach ( $tags as $tag ) {
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
		$callback = null,
		int $accepted_args = null
	) {
		if ( null === $callback ) {
			$callback_description = '';
		} else {
			$callback_description = is_callable( $callback )
				? describe_callable( $callback )
				: describe_unavailable_callable( $callback );
		}

		$hook = [
			'Tag' => $tag,
			'Priority' => null !== $priority ? (string) $priority : '',
			'Callback' => $callback_description,
			'Accepted Args' => null !== $accepted_args ? (string) $accepted_args : '',
		];

		if ( ! $this->passesFilters( [ $hook ] ) ) {
			return;
		}

		// @todo Should empty values be filtered out?
		$this->hooks[] = $hook;
	}
}
