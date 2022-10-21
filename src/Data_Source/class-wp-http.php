<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline\Timeline;
use ToyWpEventManagement\SubscriberInterface;

use function Clockwork_For_Wp\prepare_http_response;

final class Wp_Http extends DataSource implements SubscriberInterface {
	private $log;
	private $timeline;

	public function __construct( ?Log $log = null, ?Timeline $timeline = null ) {
		$this->log = $log ?: new Log();
		$this->timeline = $timeline ?: new Timeline();
	}

	public function ensure_args_have_meta( $args, $url ) {
		if ( ! \array_key_exists( '_cfw_meta', $args ) ) {
			$start = \microtime( true );

			$args['_cfw_meta'] = [
				'fingerprint' => \hash( 'md5', (string) $start . $url . \serialize( $args ) ),
				'start' => $start,
				'url' => $url,
			];
		}

		return $args;
	}

	public function finish_request( $response, $args ): void {
		if ( ! $this->args_have_meta( $args ) ) {
			$this->log_meta_error( $args );
		} elseif ( null !== $response['error'] ) {
			$this->log_request_failure( $response['error'], $args );
		} else {
			$this->log_request_success( $response['response'], $args );
			$this->end_event( $args );
		}
	}

	public function onHttpApiDebug( $response, $_, $_2, $args ): void {
		$this->finish_request( prepare_http_response( $response ), $args );
	}

	public function onHttpRequestArgs( $args, $url ) {
		$args = $this->ensure_args_have_meta( $args, $url );

		$this->start_request( $args );

		return $args;
	}

	public function onPreHttpRequest( $preempt, $args ) {
		if ( false === $preempt ) {
			return $preempt;
		}

		$this->finish_request( prepare_http_response( $preempt ), $args );

		return $preempt;
	}

	public function getSubscribedEvents(): array {
		return [
			'http_api_debug' => 'onHttpApiDebug',
			'http_request_args' => 'onHttpRequestArgs',
			'pre_http_request' => 'OnPreHttpRequest',
		];
	}

	public function resolve( Request $request ) {
		$request->log()->merge( $this->log );
		$request->timeline()->merge( $this->timeline );

		return $request;
	}

	public function start_request( $args ): void {
		if ( $this->args_have_meta( $args ) ) {
			$this->start_event( $args );
		}
	}

	private function args_have_meta( $args ) {
		return \is_array( $args )
			&& isset( $args['_cfw_meta'] )
			&& 3 === \count(
				\array_intersect(
					[ 'fingerprint', 'start', 'url' ],
					\array_keys( $args['_cfw_meta'] )
				)
			);
	}

	private function end_event( $args ): void {
		$this->timeline->event( "http_{$args['_cfw_meta']['fingerprint']}" )->end();
	}

	private function log_meta_error( $args ): void {
		$this->log->error(
			'Error in HTTP data source - meta is not set in provided args',
			\compact( 'args' )
		);
	}

	private function log_request_failure( $error, $args ): void {
		$this->log->error(
			"HTTP request for {$args['_cfw_meta']['url']} failed",
			\compact( 'args', 'error' )
		);
	}

	private function log_request_success( $response, $args ): void {
		$this->log->info(
			"HTTP request for {$args['_cfw_meta']['url']} succeeded",
			[
				'args' => $args,
				// @todo Should this be included? Just serves to increase the metadata storage requirements.
				// 'body' => $response['body'],
				'cookies' => $response['cookies'],
				'headers' => $response['headers'],
				'status' => $response['status'],
			]
		);
	}

	private function start_event( $args ): void {
		$this->timeline->event(
			"HTTP request for {$args['_cfw_meta']['url']}",
			[
				'name' => "http_{$args['_cfw_meta']['fingerprint']}",
				'start' => $args['_cfw_meta']['start'],
				'data' => $args,
			]
		);
	}
}
