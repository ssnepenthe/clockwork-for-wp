<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Rest_Api;
use Clockwork_For_Wp\Event_Management\Subscriber;

use function Clockwork_For_Wp\container;
use function Clockwork_For_Wp\prepare_rest_route;

/**
 * @internal
 */
final class Rest_Api_Subscriber implements Subscriber {
	private Rest_Api $data_source;

	public function __construct( Rest_Api $data_source ) {
		$this->data_source = $data_source;
	}

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => 'on_cfw_pre_resolve',
		];
	}

	public function on_cfw_pre_resolve(): void {
		// @todo Option for core rest endpoints to be filtered from list.
		// @todo Option for what route fields get recorded.
		foreach ( container()->get( \WP_REST_Server::class )->get_routes() as $path => $handlers ) {
			foreach ( $handlers as $handler ) {
				[ $methods, $callback, $permission_callback ] = prepare_rest_route( $handler );

				$this->data_source->add_route( $path, $methods, $callback, $permission_callback );
			}
		}
	}
}
