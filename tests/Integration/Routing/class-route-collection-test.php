<?php

namespace Clockwork_For_Wp\Tests\Integration\Routing;

use Clockwork_For_Wp\Routing\Route;
use Clockwork_For_Wp\Routing\Route_Collection;
use PHPUnit\Framework\TestCase;

class Route_Collection_Test extends TestCase {
	protected $route_collection;

	protected function setUp() : void {
		$this->route_collection = new Route_Collection();
		$this->route_collection->add(
			new Route( 'GET', 'add_method_regex', 'index.php?add_method=query', [] )
		);
		$this->route_collection->get( 'get_method_regex', 'index.php?get_method=query', [] );
		$this->route_collection->post( 'post_method_regex', 'index.php?post_method=query', [] );

	}

	protected function tearDown() : void {
		$this->route_collection = null;
	}

	/** @test */
	public function it_allows_routes_to_be_added() {
		$route = $this->route_collection->match( 'GET', 'add_method_regex' );

		$this->assertEquals( 'GET', $route->get_method() );
		$this->assertEquals( 'add_method_regex', $route->get_regex() );
	}

	/** @test */
	public function it_provides_shorthand_for_adding_get_routes() {
		$route = $this->route_collection->match( 'GET', 'get_method_regex' );

		$this->assertEquals( 'GET', $route->get_method() );
		$this->assertEquals( 'get_method_regex', $route->get_regex() );
	}

	/** @test */
	public function it_provides_shorthand_for_adding_post_routes() {
		$route = $this->route_collection->match( 'POST', 'post_method_regex' );

		$this->assertEquals( 'POST', $route->get_method() );
		$this->assertEquals( 'post_method_regex', $route->get_regex() );
	}

	/** @test */
	public function it_can_find_a_matching_route_with_method_pattern_combo() {
		$this->route_collection->get( 'extra', '', [] );

		$route = $this->route_collection->match( 'GET', 'extra' );

		$this->assertInstanceOf( Route::class, $route );
	}

	/** @test */
	public function it_returns_null_when_no_matching_route_is_found() {
		$route = $this->route_collection->match( 'PUT', 'not_real' );

		$this->assertNull( $route );
	}

	/** @test */
	public function it_provides_rewrite_rules_for_all_registered_routes() {
		$rules = $this->route_collection->get_rewrite_array();

		$this->assertEquals( [
			'add_method_regex' => 'index.php?add_method=query',
			'get_method_regex' => 'index.php?get_method=query',
			'post_method_regex' => 'index.php?post_method=query',
		], $rules );
	}

	/** @test */
	public function it_provides_query_vars_for_all_registered_routes() {
		$vars = $this->route_collection->get_query_vars();

		$this->assertEquals( [ 'add_method', 'get_method', 'post_method' ], $vars );
	}
}
