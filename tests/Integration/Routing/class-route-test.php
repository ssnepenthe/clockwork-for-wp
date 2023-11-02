<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Tests\Integration\Routing;

use Clockwork_For_Wp\Routing\Route;
use PHPUnit\Framework\TestCase;

class Route_Test extends TestCase {
	/**
	 * @test
	 */
	public function it_rebuilds_query_with_variable_prefix(): void {
		$route = new Route( '', '', 'index.php?a=b&c=d', [] );
		$route->set_prefix( 'pfx_' );

		$this->assertSame( 'index.php?a=b&c=d', $route->get_raw_query() );
		$this->assertSame( 'index.php?pfx_a=b&pfx_c=d', $route->get_query() );
	}

	/**
	 * @test
	 */
	public function it_automatically_parses_query(): void {
		$route = new Route( '', '', 'index.php?a=b&c=d', [] );

		$this->assertEquals( [
			'a' => 'b',
			'c' => 'd',
		], $route->get_query_array() );
	}

	/**
	 * @test
	 */
	public function it_automatically_parses_and_prefixes_query(): void {
		$route = new Route( '', '', 'index.php?a=b&c=d', [] );
		$route->set_prefix( 'pfx_' );

		$this->assertEquals( [
			'pfx_a' => 'b',
			'pfx_c' => 'd',
		], $route->get_query_array() );
		$this->assertEquals( [
			'a' => 'b',
			'c' => 'd',
		], $route->get_raw_query_array() );
	}

	/**
	 * @test
	 */
	public function it_provides_a_list_of_query_variables(): void {
		$route = new Route( '', '', 'index.php?a=b&c=d', [] );

		$this->assertEquals( [ 'a', 'c' ], $route->get_query_vars() );
	}

	/**
	 * @test
	 */
	public function it_provides_a_list_of_prefixed_query_variables(): void {
		$route = new Route( '', '', 'index.php?a=b&c=d', [] );
		$route->set_prefix( 'pfx_' );

		$this->assertEquals( [ 'pfx_a', 'pfx_c' ], $route->get_query_vars() );
		$this->assertEquals( [ 'a', 'c' ], $route->get_raw_query_vars() );
	}
}
