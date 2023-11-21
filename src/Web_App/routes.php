<?php

declare(strict_types=1);

use Clockwork_For_Wp\Web_App\Web_App_Controller;
use SimpleWpRouting\Router;

return static function ( Router $router ): void {
	$router->get( '__clockwork', [ Web_App_Controller::class, 'redirect' ] );
	$router->get( '__clockwork/app', [ Web_App_Controller::class, 'serve_assets' ] );
	$router->get( '__clockwork/{path:.+}', [ Web_App_Controller::class, 'serve_assets' ] );
};
