<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Helpers\StackFilter;
use Clockwork\Helpers\StackTrace;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Subscriber\Wp_Redirect_Subscriber;
use Clockwork_For_Wp\Provides_Subscriber;
use InvalidArgumentException;
use WpEventDispatcher\SubscriberInterface;

// @todo Would be nice to get this tested by our browser tests.
final class Wp_Redirect extends DataSource implements Provides_Subscriber {
	private $filtered = [
		'location' => null,
		'status' => null,
		'x-redirect-by' => null,
	];

	private $finalized = false;

	private $initial = [
		'location' => null,
		'status' => 302,
		'x-redirect-by' => 'WordPress',
	];

	private $trace;

	public function create_subscriber(): SubscriberInterface {
		return new Wp_Redirect_Subscriber( $this );
	}

	public function finalize_wp_redirect_call(): void {
		$this->finalized = true;
	}

	public function record_wp_redirect_call(): void {
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

	public function resolve( Request $request ) {
		if ( ! \is_string( $this->initial['location'] ) ) {
			return $request;
		}

		$context = [
			'Args' => $this->initial,
			'trace' => $this->trace,
		];

		$filtered = \array_diff( $this->filtered, $this->initial );

		if ( [] !== $filtered ) {
			$context['Filtered Args'] = $filtered;
		}

		$request->log()->debug( $this->build_message(), $context );

		return $request;
	}

	public function set_filtered( $key, $value ): void {
		if ( ! \array_key_exists( $key, $this->filtered ) ) {
			throw new InvalidArgumentException(
				"Cannot set invalid key {$key} on filtered args array"
			);
		}

		$this->filtered[ $key ] = $value;
	}

	public function set_initial( $key, $value ): void {
		if ( ! \array_key_exists( $key, $this->initial ) ) {
			throw new InvalidArgumentException(
				"Cannot set invalid key {$key} on initial args array"
			);
		}

		$this->initial[ $key ] = $value;
	}

	private function build_message() {
		$message = 'Call to "wp_redirect"';

		if ( ! $this->finalized ) {
			if ( ! $this->filtered['location'] ) {
				$message .= ' returned without redirecting user: "wp_redirect" filter returned a falsy value';
			} elseif ( $this->filtered['status'] < 300 || 399 < $this->filtered['status'] ) {
				$message .= ' caused call to "wp_die": "wp_redirect_status" filter returned an invalid status code';
			} else {
				$message .= ' appears to have bailed early: Reason unknown';
			}
		}

		return $message;
	}
}
