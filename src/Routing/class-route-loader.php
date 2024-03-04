<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use SimpleWpRouting\Router;

final class Route_Loader {
	private array $route_files = [];

	public function __invoke( Router $router ): void {
		foreach ( $this->route_files as $file ) {
			$loader = ( static fn ( string $f ) => include $f )( $file );

			$loader( $router );
		}
	}

	public function load( string $file ): void {
		$this->route_files[] = $file;
	}
}
