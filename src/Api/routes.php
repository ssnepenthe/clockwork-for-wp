<?php

declare(strict_types=1);

use Clockwork_For_Wp\Api\Api_Controller;
use Clockwork_For_Wp\Clockwork_Support;
use ToyWpRouting\RouteCollection;

return static function ( RouteCollection $routes ): void {
	$routes->get(
		'__clockwork/{id:[0-9-]+|latest}/extended',
		[ Api_Controller::class, 'serve_extended_json' ]
	);
	$routes->get(
		'__clockwork/{id:[0-9-]+|latest}[/{direction:next|previous}[/{count:\d+}]]',
		[ Api_Controller::class, 'serve_json' ]
	);
	$routes->put(
		'__clockwork/{id:[0-9-]+|latest}',
		[ Api_Controller::class, 'update_data' ]
	)->when( [ Clockwork_Support::class, 'is_collecting_client_metrics' ] );
	$routes->post( '__clockwork/auth', [ Api_Controller::class, 'authenticate' ] );
};
