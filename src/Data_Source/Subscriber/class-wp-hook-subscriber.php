<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Wp_Hook;
use Clockwork_For_Wp\Event_Management\Subscriber;

/**
 * @internal
 */
final class Wp_Hook_Subscriber implements Subscriber {
	private Wp_Hook $data_source;

	public function __construct( Wp_Hook $data_source ) {
		$this->data_source = $data_source;
	}

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => 'on_cfw_pre_resolve',
		];
	}

	public function on_cfw_pre_resolve( $wp_filter, $wp_actions ): void {
		$tags = $this->data_source->get_all_hooks()
			? \array_keys( $wp_filter )
			: \array_keys( $wp_actions );

		foreach ( $tags as $tag ) {
			if ( isset( $wp_filter[ $tag ] ) ) {
				foreach ( $wp_filter[ $tag ] as $priority => $callbacks ) {
					foreach ( $callbacks as $callback ) {
						$this->data_source->add_hook(
							$tag,
							$priority,
							$callback['function'],
							$callback['accepted_args']
						);
					}
				}
			} else {
				$this->data_source->add_hook( $tag );
			}
		}
	}
}
