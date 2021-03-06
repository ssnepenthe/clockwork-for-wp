<?php

namespace Clockwork_For_Wp\Tests\Integration\Routing;

use Clockwork_For_Wp\Routing\Route;
use PHPUnit\Framework\TestCase;

class Route_Test extends TestCase {
	/** @test */
	public function it_automatically_parses_query() {
		$route = new Route( '', '', 'index.php?a=b&c=d', [] );

		$this->assertEquals( [
			'a' => 'b',
			'c' => 'd',
		], $route->get_query_array() );
	}
}
