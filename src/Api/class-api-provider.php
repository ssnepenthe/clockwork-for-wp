<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Api;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Metadata;

final class Api_Provider extends Base_Provider {
	public function boot(): void {
		if ( $this->plugin->is_enabled() ) {
			parent::boot();
		}
	}

	public function register(): void {
		$this->plugin[ Api_Controller::class ] = function () {
			return new Api_Controller(
				$this->plugin[ AuthenticatorInterface::class ],
				$this->plugin[ Metadata::class ],
				$this->plugin[ IncomingRequest::class ]
			);
		};

		$this->plugin[ Api_Subscriber::class ] = static function () {
			return new Api_Subscriber();
		};
	}

	protected function subscribers(): array {
		return [ Api_Subscriber::class ];
	}
}
