<?php

namespace Clockwork_For_Wp\Tests\Integration\Routing;

use Clockwork_For_Wp\Routing\Route;
use Clockwork_For_Wp\Routing\Route_Collection;
use PHPUnit\Framework\TestCase;

class Route_Collection_Test extends TestCase {
	private function get_route_collection( $prefix = '' ) {
		$route_collection = new Route_Collection( $prefix );
		$route_collection->add(
			new Route( 'GET', 'add_method_regex', 'index.php?add_method=query', [] )
		);
		$route_collection->get( 'get_method_regex', 'index.php?get_method=query', [] );
		$route_collection->post( 'post_method_regex', 'index.php?post_method=query', [] );
		$route_collection->put( 'put_method_regex', 'index.php?put_method=query', [] );

		return $route_collection;
	}

	/** @test */
	public function it_allows_routes_to_be_added() {
		$route = $this->get_route_collection()->match( 'GET', 'add_method_regex' );

		$this->assertEquals( 'GET', $route->get_method() );
		$this->assertEquals( 'add_method_regex', $route->get_regex() );
	}

	/** @test */
	public function it_sets_prefix_on_all_added_routes() {
		$prefixed_query_vars = $this->get_route_collection( 'pfx_' )->get_query_vars();

		$this->assertSame(
			[ 'pfx_add_method', 'pfx_get_method', 'pfx_post_method', 'pfx_put_method' ],
			$prefixed_query_vars
		);
	}

	/** @test */
	public function it_provides_shorthand_for_adding_get_routes() {
		$route = $this->get_route_collection()->match( 'GET', 'get_method_regex' );

		$this->assertEquals( 'GET', $route->get_method() );
		$this->assertEquals( 'get_method_regex', $route->get_regex() );
	}

	/** @test */
	public function it_provides_shorthand_for_adding_post_routes() {
		$route = $this->get_route_collection()->match( 'POST', 'post_method_regex' );

		$this->assertEquals( 'POST', $route->get_method() );
		$this->assertEquals( 'post_method_regex', $route->get_regex() );
	}

	/** @test */
	public function it_provides_shorthand_for_adding_put_routes() {
		$route = $this->get_route_collection()->match( 'PUT', 'put_method_regex' );

		$this->assertEquals( 'PUT', $route->get_method() );
		$this->assertEquals( 'put_method_regex', $route->get_regex() );
	}

	/** @test */
	public function it_can_find_a_matching_route_with_method_pattern_combo() {
		$route = $this->get_route_collection()->match( 'GET', 'add_method_regex' );

		$this->assertInstanceOf( Route::class, $route );
	}

	/** @test */
	public function it_returns_null_when_no_matching_route_is_found() {
		$route = $this->get_route_collection()->match( 'PUT', 'not_real' );

		$this->assertNull( $route );
	}

	/** @test */
	public function it_provides_rewrite_rules_for_all_registered_routes() {
		$rules = $this->get_route_collection()->get_rewrite_array();

		$this->assertEquals( [
			'add_method_regex' => 'index.php?add_method=query',
			'get_method_regex' => 'index.php?get_method=query',
			'post_method_regex' => 'index.php?post_method=query',
			'put_method_regex' => 'index.php?put_method=query',
		], $rules );

		$prefixed_rules = $this->get_route_collection( 'pfx_' )->get_rewrite_array();

		$this->assertEquals( [
			'add_method_regex' => 'index.php?pfx_add_method=query',
			'get_method_regex' => 'index.php?pfx_get_method=query',
			'post_method_regex' => 'index.php?pfx_post_method=query',
			'put_method_regex' => 'index.php?pfx_put_method=query',
		], $prefixed_rules );
	}

	/** @test */
	public function it_provides_rewrite_rules_for_a_given_request_method() {
		$rules = $this->get_route_collection()->get_rewrite_array_for_method( 'GET' );

		$this->assertEquals( [
			'add_method_regex' => 'index.php?add_method=query',
			'get_method_regex' => 'index.php?get_method=query',
		], $rules );

		$prefixed_rules = $this->get_route_collection( 'pfx_' )
			->get_rewrite_array_for_method( 'POST' );

		$this->assertEquals( [
			'post_method_regex' => 'index.php?pfx_post_method=query',
		], $prefixed_rules );
	}

	/** @test */
	public function it_provides_query_vars_for_all_registered_routes() {
		$vars = $this->get_route_collection()->get_query_vars();

		$this->assertEquals( [ 'add_method', 'get_method', 'post_method', 'put_method' ], $vars );

		$prefixed_vars = $this->get_route_collection( 'pfx_' )->get_query_vars();

		$this->assertEquals(
			[ 'pfx_add_method', 'pfx_get_method', 'pfx_post_method', 'pfx_put_method' ],
			$prefixed_vars
		);
	}
}
