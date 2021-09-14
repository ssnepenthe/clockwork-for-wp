<?php

namespace Clockwork_For_Wp\Tests\Integration\Routing;

use Clockwork_For_Wp\Routing\Route;
use Clockwork_For_Wp\Routing\Route_Handler_Invoker;
use Invoker\Invoker;
use PHPUnit\Framework\TestCase;

class Route_Handler_Invoker_Test extends TestCase {
	/** @test */
	public function it_does_not_inject_any_additional_params_by_default() {
		$invoker = new Route_Handler_Invoker( new Invoker );

		$result = $invoker->invoke_handler( new Route( '', '', [], function( ...$params ) {
			return $params;
		} ) );

		$this->assertCount( 0, $result );
	}

	/** @test */
	public function it_provides_additional_params_to_route_handler() {
		$invoker = new Route_Handler_Invoker( new Invoker, '', function() {
			return [ 'a' => 1, 'b' => 2, 'c' => 3 ];
		} );

		$result = $invoker->invoke_handler( new Route( '', '', [], function( $a, $b, $c ) {
			return [ $c, $b, $a ];
		} ) );

		$this->assertSame( [ 3, 2, 1 ], $result );
	}

	/** @test */
	public function it_binds_param_resolver_to_invoker_instance() {
		$phpunit = $this;
		$invoker = new Route_Handler_Invoker( new Invoker, '', function() use ( $phpunit ) {
			$phpunit->assertInstanceOf( Route_Handler_Invoker::class, $this );

			return [];
		} );

		$result = $invoker->invoke_handler( new Route( '', '', [], function() {} ) );
	}

	/** @test */
	public function it_correctly_strips_param_prefixes() {
		$invoker = new Route_Handler_Invoker( new Invoker, 'pfx_' );

		$this->assertSame( 'apples', $invoker->strip_param_prefix( 'pfx_apples' ) );
		$this->assertSame( 'app_pfx_les', $invoker->strip_param_prefix( 'app_pfx_les' ) );
	}
}
