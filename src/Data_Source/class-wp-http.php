<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\DataSource\DataSource;

class Wp_Http extends DataSource {
	protected $log;
	protected $timeline;

	public function __construct( Log $log = null, Timeline $timeline = null ) {
		$this->log = $log ?: new Log();
		$this->timeline = $timeline ?: new Timeline();
	}

	public function resolve( Request $request ) {
		$request->log = array_merge( $request->log, $this->log->toArray() );
		$request->timelineData = array_merge( $request->timelineData, $this->timeline->finalize() );

		return $request;
	}

	public function listen_to_events() {
		// @todo Should these trigger later than 10?
		add_filter( 'http_request_args', function( $args, $url ) {
			$args = $this->add_meta_to_args( $args, $url );

			$this->record_request_start( $args );

			return $args;
		}, 10, 2 );

		add_filter( 'pre_http_request', function( $preempt, $args, $url ) {
			if ( false === $preempt ) {
				return $preempt;
			}

			if ( ! $this->args_have_meta( $args ) ) {
				$this->record_meta_error( $args );

				return $preempt;
			}

			if ( is_wp_error( $preempt ) ) {
				$this->record_request_failure( $args );
			}

			$this->record_request_finish( $args );

			return $preempt;
		}, 10, 3 );

		add_action( 'http_api_debug', function( $response, $context, $class, $args, $url ) {
			if ( ! $this->args_have_meta( $args ) ) {
				return $this->record_meta_error( $args );
			}

			if ( is_wp_error( $response ) ) {
				$this->record_request_failure( $response, $args );
			}

			$this->record_request_finish( $args );
		}, 10, 5 );
	}

	protected function add_meta_to_args( $args, $url ) {
		$start = microtime( true );
		$fingerprint = hash( 'md5', $start . $url . serialize( $args ) );

		return array_merge( $args, [ '_cfw_meta' => compact( 'fingerprint', 'url', 'start' ) ] );
	}

	protected function args_have_meta( $args ) {
		return is_array( $args )
			&& isset( $args['_cfw_meta'] )
			&& 3 === count( array_intersect(
				[ 'fingerprint', 'start', 'url' ],
				array_keys( $args['_cfw_meta'] )
			) );
	}

	protected function record_meta_error( $args ) {
		$this->log->error(
			'Error in HTTP data source - meta is not set in provided args',
			compact( 'args' )
		);
	}

	protected function record_request_failure( $error, $args ) {
		$this->log->error( "HTTP request for {$args['_cfw_meta']['url']} failed", [
			'args' => $args,
			'error' => $error,
		] );
	}

	protected function record_request_finish( $args ) {
		$this->timeline->endEvent( "http_{$args['_cfw_meta']['fingerprint']}" );
	}

	protected function record_request_start( $args ) {
		$this->timeline->startEvent(
			"http_{$args['_cfw_meta']['fingerprint']}",
			"HTTP request for {$args['_cfw_meta']['url']}",
			$args['_cfw_meta']['start'],
			$args
		);
	}
}
