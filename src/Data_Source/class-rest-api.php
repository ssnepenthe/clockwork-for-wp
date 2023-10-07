<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Subscriber\Rest_Api_Subscriber;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Provides_Subscriber;
use function Clockwork_For_Wp\describe_callable;

final class Rest_Api extends DataSource implements Provides_Subscriber {
	private $routes = [];

	public function add_route( $path, $methods, $callback = null, $permission_callback = null ) {
		if ( \is_array( $methods ) ) {
			$methods = \implode( ', ', $methods );
		}

		if ( null === $callback ) {
			$callback = '';
		} else {
			$callback = describe_callable( $callback );
		}

		if ( null === $permission_callback ) {
			$permission_callback = '';
		} else {
			$permission_callback = describe_callable( $permission_callback );
		}

		// @todo Filter null values?
		$this->routes[] = [
			'Path' => $path,
			'Methods' => $methods,
			'Callback' => $callback,
			'Permission Callback' => $permission_callback,
		];

		return $this;
	}

	public function create_subscriber(): Subscriber {
		return new Rest_Api_Subscriber( $this );
	}

	public function resolve( Request $request ) {
		if ( \count( $this->routes ) > 0 ) {
			$request->userData( 'Routing' )->table( 'REST Routes', $this->routes );
		}

		return $request;
	}
}
