<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Event_Management;

use Clockwork_For_Wp\Base_Provider;
use Invoker\Invoker;
use Pimple\Container;

/**
 * @internal
 */
final class Event_Management_Provider extends Base_Provider {
	public function register(): void {
		$this->plugin->get_pimple()[ Event_Manager::class ] = static function ( Container $pimple ) {
			return new Event_Manager( $pimple[ Invoker::class ] );
		};
	}
}