<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork_For_Wp\Event_Management\Subscriber;

final class Plugin_Subscriber implements Subscriber {
	public function get_subscribed_events(): array {
		return [
			'redirect_canonical' => 'prevent_trailing_slash_redirect',
		];
	}

	public function prevent_trailing_slash_redirect( $redirect, $requested ) {
		$clockwork = \home_url( '__clockwork' );

		if ( \mb_substr( $requested, 0, \mb_strlen( $clockwork ) ) === $clockwork ) {
			return $requested;
		}

		return $redirect;
	}
}
