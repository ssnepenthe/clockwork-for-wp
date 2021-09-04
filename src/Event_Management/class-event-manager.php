<?php

namespace Clockwork_For_Wp\Event_Management;

use Closure;
use InvalidArgumentException;
use Invoker\Invoker;

class Event_Manager {
	public const DEFAULT_EVENT = 10;
	public const EARLY_EVENT = -999;
	public const LATE_EVENT = 999;
	protected $invoker;

	public function __construct( Invoker $invoker ) {
		$this->invoker = $invoker;
	}

	public function attach( Subscriber $subscriber ) {
		foreach ( $subscriber->get_subscribed_events() as $tag => $args ) {
			if ( is_string( $args ) || $args instanceof Closure ) {
				$args = [ $args ];
			}

			if ( ! is_array( $args ) ) {
				$subscriber_class = get_class( $subscriber );
				$args_type = gettype( $args );

				throw new InvalidArgumentException(
					"Invalid args provided by {$subscriber_class} for tag {$tag} - "
						. "Expected string, closure or array, got {$args_type}"
				);
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

	public function filter( $tag, ...$args ) {
		return \apply_filters( $tag, ...$args );
	}

	public function on( $tag, $callable, $priority = self::DEFAULT_EVENT ) {
		\add_action( $tag, function ( ...$args ) use ( $callable ) {
			return $this->invoker->call( $callable, $args );
		}, $priority, 999 );

		return $this;
	}

	public function trigger( $tag, ...$args ) {
		\do_action( $tag, ...$args );

		return $this;
	}

	protected function attach_subscriber_callback(
		Subscriber $subscriber,
		string $tag,
		array $args
	) {
		if ( ! isset( $args[0] ) ) {
			$subscriber_class = get_class( $subscriber );

			throw new InvalidArgumentException(
				"Incorrect array shape provided by {$subscriber_class} for tag {$tag} - "
					. 'callback expected at index 0'
			);
		}

		if ( is_string( $args[0] ) ) {
			$callable = [ $subscriber, $args[0] ];
		} elseif ( $args[0] instanceof Closure ) {
			$callable = $args[0];
		} else {
			$subscriber_class = get_class( $subscriber );
			$callback_type = gettype( $args[0] );

			throw new InvalidArgumentException(
				"Invalid args provided by {$subscriber_class} for tag {$tag} - "
					. "callback must be a string or closure, got {$callback_type}"
			);
		}

		return $this->on( $tag, $callable, $args[1] ?? self::DEFAULT_EVENT );
	}
}
