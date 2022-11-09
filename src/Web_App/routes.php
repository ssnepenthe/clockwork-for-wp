<?php

use Clockwork_For_Wp\Web_App\Web_App_Controller;
use ToyWpRouting\RouteCollection;

return static function ( RouteCollection $routes ) {
    $routes->get( '__clockwork', [ Web_App_Controller::class, 'serve_redirect' ] );
    $routes->get( '__clockwork/app', [ Web_App_Controller::class, 'serve_index' ] );
    $routes->get( '__clockwork/{file:[^\/]+\.(html|json|js)}', [ Web_App_Controller::class, 'serve_asset' ] );
};
