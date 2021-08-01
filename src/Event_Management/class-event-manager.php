<?php

namespace Clockwork_For_Wp\Event_Management;

use Closure;
use InvalidArgumentException;
use Invoker\Invoker;

class Event_Manager {
	const EARLY_EVENT = -999;
	const DEFAULT_EVENT = 10;
	const LATE_EVENT = 999;

	protected $invoker;

	// @todo Set up invoker.
	public function __construct( Invoker $invoker ) {
		$this->invoker = $invoker;
	}

	public function attach( Subscriber $subscriber ) {
		foreach ( $subscriber->get_subscribed_events() as $tag => $args ) {
			if ( is_string( $args ) || $args instanceof Closure ) {
				$args = [ $args ];
			}

			if ( ! is_array( $args ) ) {
				throw new InvalidArgumentException( '@todo' );
			}

			if ( isset( $args[0] ) && ( is_string( $args[0] ) || $args[0] instanceof Closure ) ) {
				$args = [ $args ];
			}

			foreach ( $args as $arg ) {
				$this->attach_subscriber_callback( $subscriber, $tag, $arg );
			}
		}

		return $this;
	}

	public function on( $tag, $callable, $priority = self::DEFAULT_EVENT ) {
		\add_action( $tag, function( ...$args ) use ( $callable ) {
			return $this->invoker->call( $callable, $args );
		}, $priority, 999 );

		return $this;
	}

	public function trigger( $tag, ...$args ) {
		\do_action( $tag, ...$args );

		return $this;
	}

	public function filter( $tag, ...$args ) {
		return \apply_filters( $tag, ...$args );
	}

	protected function attach_subscriber_callback(
		Subscriber $subscriber,
		string $tag,
		array $args
	) {
		if ( ! isset( $args[0] ) ) {
			throw new InvalidArgumentException( '@todo' );
		}

		if ( is_string( $args[0] ) ) {
			$callable = [ $subscriber, $args[0] ];
		} else if ( $args[0] instanceof Closure ) {
			$callable = $args[0];
		} else {
			throw new InvalidArgumentException( '@todo' );
		}

		return $this->on( $tag, $callable, $args[1] ?? self::DEFAULT_EVENT );
	}
}
