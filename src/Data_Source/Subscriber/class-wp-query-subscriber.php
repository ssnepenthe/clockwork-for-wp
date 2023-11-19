<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Wp_Query;
use Clockwork_For_Wp\Globals;
use WpEventDispatcher\SubscriberInterface;

/**
 * @internal
 */
final class Wp_Query_Subscriber implements SubscriberInterface {
	private Wp_Query $data_source;

	public function __construct( Wp_Query $data_source ) {
		$this->data_source = $data_source;
	}

	public function getSubscribedEvents(): array {
		return [
			'cfw_pre_resolve' => 'on_cfw_pre_resolve',
		];
	}

	public function on_cfw_pre_resolve(): void {
		// @todo I think there is a flaw in this logic... It is poorly adapted from query monitor.
		// @todo Move to event manager?
		$plugin_vars = \apply_filters( 'query_vars', [] );

		$query_vars = \array_filter(
			Globals::get( 'wp_query' )->query_vars,
			static function ( $value, $key ) use ( $plugin_vars ) {
				return ( isset( $plugin_vars[ $key ] ) && '' !== $value ) || ! empty( $value );
			},
			\ARRAY_FILTER_USE_BOTH
		);

		$this->data_source->set_query_vars( $query_vars );
	}
}
