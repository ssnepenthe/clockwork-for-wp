<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use SimpleWpRouting\Router;

final class Route_Loader {
	private array $route_files = [];

	private Router $router;

	public function __construct( Router $router ) {
		$this->router = $router;
	}

	public function initialize(): void {
		$this->router->initialize( fn ( Router $router ) => $this->do_initialize( $router ) );
	}

	public function load( string $file ): void {
		$this->route_files[] = $file;
	}

	private function do_initialize( Router $router ): void {
		foreach ( $this->route_files as $file ) {
			$loader = ( static fn ( string $f ) => include $f )( $file );

			$loader( $router );
		}
	}
}
