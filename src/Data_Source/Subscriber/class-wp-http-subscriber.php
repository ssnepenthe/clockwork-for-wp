<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Wp_Http;
use WpEventDispatcher\SubscriberInterface;

use function Clockwork_For_Wp\prepare_http_response;

/**
 * @internal
 */
final class Wp_Http_Subscriber implements SubscriberInterface {
	private Wp_Http $data_source;

	public function __construct( Wp_Http $data_source ) {
		$this->data_source = $data_source;
	}

	public function getSubscribedEvents(): array {
		return [
			'http_api_debug' => 'on_http_api_debug',
			'http_request_args' => 'on_http_request_args',
			'pre_http_request' => 'on_pre_http_request',
		];
	}

	public function on_http_api_debug( $response, $_, $_2, $args ): void {
		$this->data_source->finish_request( prepare_http_response( $response ), $args );
	}

	public function on_http_request_args( $args, $url ) {
		$args = $this->data_source->ensure_args_have_meta( $args, $url );

		$this->data_source->start_request( $args );

		return $args;
	}

	public function on_pre_http_request( $preempt, $args ) {
		if ( false === $preempt ) {
			return $preempt;
		}

		$this->data_source->finish_request( prepare_http_response( $preempt ), $args );

		return $preempt;
	}
}
