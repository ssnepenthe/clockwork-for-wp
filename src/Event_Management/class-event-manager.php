<?php

namespace Clockwork_For_Wp\Event_Management;

use Closure;
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

	public function attach( $subscriber ) {
		if ( $subscriber instanceof Subscriber ) {
			$subscriber->subscribe_to_events( $this );
		} else if ( $subscriber instanceof Managed_Subscriber ) {
			foreach ( $subscriber->get_subscribed_events() as $tag => $sub ) {
				if ( is_string( $sub ) ) {
					$callable = [ $subscriber, $sub ];
					$priority = static::DEFAULT_EVENT;
				} else {
					$callable = [ $subscriber, $sub[0] ];
					$priority = $sub[1];
				}

				$this->on( $tag, $callable, $priority );
			}
		} else {
			throw new \InvalidArgumentException( '@todo' );
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
}
