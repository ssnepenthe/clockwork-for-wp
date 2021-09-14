<?php

namespace Clockwork_For_Wp\Tests\Unit\Routing;

use Clockwork_For_Wp\Routing\Route;
use PHPUnit\Framework\TestCase;

class Route_Test extends TestCase {
	/** @test */
	public function it_automatically_builds_query_string() {
		$route = new Route( '', '', [ 'a' => 'b', 'c' => 'd' ], [] );

		$this->assertSame( 'index.php?a=b&c=d', $route->get_query() );
	}

	/** @test */
	public function it_provides_a_list_of_query_variables() {
		$route = new Route( '', '', [ 'a' => 'b', 'c' => 'd' ], [] );

		$this->assertSame( [ 'a', 'c' ], $route->get_query_vars() );
	}
}
