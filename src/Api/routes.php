<?php

declare(strict_types=1);

use Clockwork_For_Wp\Api\Api_Controller;
use Clockwork_For_Wp\Is;
use SimpleWpRouting\Router;

return static function ( Router $router ): void {
	$router->post( '__clockwork/auth', [ Api_Controller::class, 'authenticate' ] );
	$router->get( '__clockwork/{id:[0-9-]+|latest}/extended', [ Api_Controller::class, 'serve_extended_json' ] );
	$router->get(
		'__clockwork/{id:[0-9-]+|latest}[/{direction:next|previous}[/{count:\d+}]]',
		[ Api_Controller::class, 'serve_json' ]
	);
	$router->put( '__clockwork/{id:[0-9-]+|latest}', [ Api_Controller::class, 'update_data' ] )
		->setIsActiveCallback( [ Is::class, 'collecting_client_metrics' ] );
};
