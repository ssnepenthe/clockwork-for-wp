<?php

declare(strict_types=1);

use Clockwork_For_Wp\Web_App\Web_App_Controller;
use ToyWpRouting\RouteCollection;

return static function ( RouteCollection $routes ): void {
	$routes->get( '__clockwork', [ Web_App_Controller::class, 'serve_redirect' ] );
	$routes->get( '__clockwork/app', [ Web_App_Controller::class, 'serve_index' ] );
	// @todo configure nginx and re-test
	$routes->get(
		'__clockwork/{file:[^\/]+\.(html|json|js)}',
		[ Web_App_Controller::class, 'serve_asset' ]
	);
};
