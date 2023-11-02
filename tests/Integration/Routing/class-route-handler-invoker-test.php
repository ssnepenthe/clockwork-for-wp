<?php

namespace Clockwork_For_Wp\Tests\Integration\Routing;

use Clockwork_For_Wp\Routing\Route;
use Clockwork_For_Wp\Routing\Route_Handler_Invoker;
use PHPUnit\Framework\TestCase;

class Route_Handler_Invoker_Test extends TestCase {
	/**
	 * @test
	 */
	public function it_does_not_inject_any_additional_params_by_default(): void {
		$invoker = new Route_Handler_Invoker();

		$result = $invoker->invoke_handler( new Route( '', '', '', static fn( $params ) => $params ) );

		$this->assertCount( 0, $result );
	}

	/**
	 * @test
	 */
	public function it_provides_additional_params_to_route_handler(): void {
		$params = [ 'a' => 1, 'b' => 2, 'c' => 3 ];

		$invoker = new Route_Handler_Invoker( '', static fn() => $params );

		$result = $invoker->invoke_handler( new Route( '', '', '', static fn( $params ) => $params ) );

		$this->assertSame( $params, $result );
	}

	/**
	 * @test
	 */
	public function it_binds_param_resolver_to_invoker_instance(): void {
		$invoker = new Route_Handler_Invoker(
			'abc_',
			fn() => [ $this->strip_param_prefix( 'abc_apples' ) => 'bananas' ]
		);

		$result = $invoker->invoke_handler( new Route( '', '', '', static fn( $params ) => $params ) );

		$this->assertSame( [ 'apples' => 'bananas' ], $result );
	}

	/**
	 * @test
	 */
	public function it_correctly_strips_param_prefixes(): void {
		$invoker = new Route_Handler_Invoker( 'pfx_' );

		$this->assertSame( 'apples', $invoker->strip_param_prefix( 'pfx_apples' ) );
		$this->assertSame( 'app_pfx_les', $invoker->strip_param_prefix( 'app_pfx_les' ) );
	}

	/**
	 * @test
	 */
	public function it_can_resolve_non_callable_handlers(): void {
		$handler = static fn( $params ) => 'called successfully';

		$invoker = new Route_Handler_Invoker(
			'',
			null,
			static fn( $callable ) => ( 'handler' === $callable ) ? $handler : $callable
		);

		$this->assertSame( 'called successfully', $invoker->invoke_handler( new Route( '', '', '', 'handler' ) ) );
	}
}
