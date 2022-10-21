<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use ToyWpEventManagement\SubscriberInterface;

final class Plugin_Subscriber implements SubscriberInterface {
	public function getSubscribedEvents(): array {
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
