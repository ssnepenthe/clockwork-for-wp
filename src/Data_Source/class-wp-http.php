<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

use function Clockwork_For_Wp\prepare_http_response;

class Wp_Http extends DataSource implements Subscriber {
	protected $log;
	protected $timeline;

	public function __construct( Log $log = null, Timeline $timeline = null ) {
		$this->log = $log ?: new Log();
		$this->timeline = $timeline ?: new Timeline();
	}

	public function subscribe_to_events( Event_Manager $event_manager ) : void {
		$event_manager
			->on( 'http_api_debug', function( $response, $_, $_2, $args ) {
				$this->finish_request( prepare_http_response( $response ), $args );
			} )
			->on( 'http_request_args', function( $args, $url ) {
				$args = $this->ensure_args_have_meta( $args, $url );

				$this->start_request( $args );

				return $args;
			} )
			->on( 'pre_http_request', function( $preempt, $args ) {
				if ( false === $preempt ) {
					return $preempt;
				}

				$this->finish_request( prepare_http_response( $preempt ), $args );

				return $preempt;
			} );
	}

	public function resolve( Request $request ) {
		$request->log = array_merge( $request->log, $this->log->toArray() );
		$request->timelineData = array_merge( $request->timelineData, $this->timeline->finalize() );

		return $request;
	}

	public function start_request( $args ) {
		if ( $this->args_have_meta( $args ) ) {
			$this->start_event( $args );
		}
	}

	public function finish_request( $response, $args ) {
		if ( ! $this->args_have_meta( $args ) ) {
			$this->log_meta_error( $args );
		} else if ( null !== $response['error'] ) {
			$this->log_request_failure( $response['error'], $args );
		} else {
			$this->log_request_success( $response['response'], $args );
			$this->end_event( $args );
		}
	}

	protected function log_request_failure( $error, $args ) {
		$this->log->error(
			"HTTP request for {$args['_cfw_meta']['url']} failed",
			compact( 'args', 'error' )
		);
	}

	protected function log_request_success( $response, $args ) {
		$this->log->info( "HTTP request for {$args['_cfw_meta']['url']} succeeded", [
			'args' => $args,
			// @todo Should this be included? Just serves to increase the metadata storage requirements.
			// 'body' => $response['body'],
			'cookies' => $response['cookies'],
			'headers' => $response['headers'],
			'status' => $response['status'],
		] );
	}

	protected function log_meta_error( $args ) {
		$this->log->error(
			'Error in HTTP data source - meta is not set in provided args',
			compact( 'args' )
		);
	}

	protected function start_event( $args ) {
		$this->timeline->startEvent(
			"http_{$args['_cfw_meta']['fingerprint']}",
			"HTTP request for {$args['_cfw_meta']['url']}",
			$args['_cfw_meta']['start'],
			$args
		);
	}

	protected function end_event( $args ) {
		$this->timeline->endEvent( "http_{$args['_cfw_meta']['fingerprint']}" );
	}

	public function ensure_args_have_meta( $args, $url ) {
		if ( ! array_key_exists( '_cfw_meta', $args ) ) {
			$start = microtime( true );

			$args['_cfw_meta'] = [
				'fingerprint' => hash( 'md5', (string) $start . $url . serialize( $args ) ),
				'start' => $start,
				'url' => $url,
			];
		}

		return $args;
	}

	protected function args_have_meta( $args ) {
		return is_array( $args )
			&& isset( $args['_cfw_meta'] )
			&& 3 === count( array_intersect(
				[ 'fingerprint', 'start', 'url' ],
				array_keys( $args['_cfw_meta'] )
			) );
	}
}
