<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Subscriber;

use function Clockwork_For_Wp\describe_callable;

class Wp_Hook extends DataSource implements Subscriber {
	protected $hooks = [];

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
		// @todo Should empty values be filtered out?
		$this->hooks[] = [
			'Tag' => (string) $tag,
			'Priority' => null !== $priority ? (string) $priority : '',
			'Callback' => null !== $callback ? describe_callable( $callback ) : '',
			'Accepted Args' => null !== $accepted_args ? (string) $accepted_args : '',
		];
	}
}
