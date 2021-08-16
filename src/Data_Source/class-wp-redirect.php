<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Helpers\StackFilter;
use Clockwork\Helpers\StackTrace;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

// @todo Would be nice to get this tested by our browser tests.
class Wp_Redirect extends DataSource implements Subscriber {
	protected $initial =  [
		'location' => null,
		'status' => 302,
		'x-redirect-by' => 'WordPress',
	];
	protected $filtered = [
		'location' => null,
		'status' => null,
		'x-redirect-by' => null
	];
	protected $finalized = false;
	protected $trace;

	public function get_subscribed_events() : array {
		return [
			'wp_redirect' => [
				function( $location ) {
					$this->record_wp_redirect_call();
					$this->set_filtered( 'location', $location );

					return $location;
				},
				Event_Manager::LATE_EVENT
			],
			'wp_redirect_status' => [
				function( $status ) {
					$this->set_filtered( 'status', $status );

					return $status;
				},
				Event_Manager::LATE_EVENT
			],
			'x_redirect_by' => [
				function( $x_redirect_by ) {
					$this->set_filtered( 'x-redirect-by', $x_redirect_by );
					$this->finalize_wp_redirect_call();

					return $x_redirect_by;
				},
				Event_Manager::LATE_EVENT
			],
		];
	}

	public function resolve( Request $request ) {
		if ( ! is_string( $this->initial['location'] ) ) {
			return $request;
		}

		$context = [
			'Args' => $this->initial,
			'trace' => $this->trace,
		];

		$filtered = array_diff( $this->filtered, $this->initial );

		if ( [] !== $filtered ) {
			$context['Filtered Args'] = $filtered;
		}

		$request->log()->debug( $this->build_message(), $context );

		return $request;
	}

	public function finalize_wp_redirect_call() {
		$this->finalized = true;
	}

	public function set_filtered( $key, $value ) {
		if ( ! array_key_exists( $key, $this->filtered ) ) {
			throw new \InvalidArgumentException(
				"Cannot set invalid key {$key} on filtered args array"
			);
		}

		$this->filtered[ $key ] = $value;
	}

	public function set_initial( $key, $value ) {
		if ( ! array_key_exists( $key, $this->initial ) ) {
			throw new \InvalidArgumentException(
				"Cannot set invalid key {$key} on initial args array"
			);
		}

		$this->initial[ $key ] = $value;
	}

	protected function build_message() {
		$message = 'Call to "wp_redirect"';

		if ( ! $this->finalized ) {
			if ( ! $this->filtered['location'] ) {
				$message .= ' returned without redirecting user: "wp_redirect" filter returned a falsey value';
			} else if ( $this->filtered['status'] < 300 || 399 < $this->filtered['status'] ) {
				$message .= ' caused call to "wp_die": "wp_redirect_status" filter returned an invalid status code';
			} else {
				$message .= ' appears to have bailed early: Reason unknown';
			}
		}

		return $message;
	}

	protected function record_wp_redirect_call() {
		// @todo Set limit? Limit from config is only enforced by the serializer when the request is resolved.
		// Seems like limitless trace with arguments might end up causing some memory issues...
		$this->trace = StackTrace::get( [ 'arguments' => true ] )->skip(
			// @todo Not sure this is the best/correct way to use this...
			StackFilter::make()->isFunction( 'wp_redirect' )
		);

		$initial_args = $this->trace->first()->args;

		if ( isset( $initial_args[0] ) ) {
			$this->set_initial( 'location', $initial_args[0] );
		}

		if ( isset( $initial_args[1] ) ) {
			$this->set_initial( 'status', $initial_args[1] );
		}

		if ( isset( $initial_args[2] ) ) {
			$this->set_initial( 'x-redirect-by', $initial_args[2] );
		}
	}
}
