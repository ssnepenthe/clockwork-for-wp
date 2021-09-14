<?php

namespace Clockwork_For_Wp\Tests\Integration\Routing;

use Clockwork_For_Wp\Routing\Fastroute_Converter;
use Clockwork_For_Wp\Routing\Route;
use Clockwork_For_Wp\Routing\Route_Collection;
use PHPUnit\Framework\TestCase;

class Route_Collection_Test extends TestCase {
	protected function get_route_collection() {
		$route_collection = new Route_Collection( new Fastroute_Converter() );
		$route_collection->add(
			new Route( 'GET', '/add/([^/]+)', [ 'add_param' => '$matches[1]' ], [] )
		);
		$route_collection->get( '/get/{get_param}', [] );
		$route_collection->post( '/post/{post_param}', [] );
		$route_collection->put( '/put/{put_param}', [] );

		return $route_collection;
	}

	/** @test */
	public function it_allows_routes_to_be_added() {
		$route = $this->get_route_collection()->match( 'GET', '/add/([^/]+)' );

		$this->assertSame( 'GET', $route->get_method() );
		$this->assertSame( '/add/([^/]+)', $route->get_regex() );
	}

	/** @test */
	public function it_provides_shorthand_for_adding_get_routes() {
		$route = $this->get_route_collection()->match( 'GET', '^/get/([^/]+)$' );

		$this->assertSame( 'GET', $route->get_method() );
		$this->assertSame( '^/get/([^/]+)$', $route->get_regex() );
	}

	/** @test */
	public function it_provides_shorthand_for_adding_post_routes() {
		$route = $this->get_route_collection()->match( 'POST', '^/post/([^/]+)$' );

		$this->assertSame( 'POST', $route->get_method() );
		$this->assertSame( '^/post/([^/]+)$', $route->get_regex() );
	}

	/** @test */
	public function it_provides_shorthand_for_adding_put_routes() {
		$route = $this->get_route_collection()->match( 'PUT', '^/put/([^/]+)$' );

		$this->assertSame( 'PUT', $route->get_method() );
		$this->assertSame( '^/put/([^/]+)$', $route->get_regex() );
	}

	/** @test */
	public function it_can_find_a_matching_route_with_method_pattern_combo() {
		$route = $this->get_route_collection()->match( 'GET', '/add/([^/]+)' );

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

		$this->assertSame( [
			'/add/([^/]+)' => 'index.php?add_param=$matches[1]',
			'^/get/([^/]+)$' => 'index.php?get_param=$matches[1]&matched_route=94e2c88860f0ee9488ad3af086598b18',
			'^/post/([^/]+)$' => 'index.php?post_param=$matches[1]&matched_route=a1f9fc4f6f3c7fb96ec87d2068d10821',
			'^/put/([^/]+)$' => 'index.php?put_param=$matches[1]&matched_route=ded9f917fad95da64abfb3f7ffc3e277',
		], $rules );
	}

	/** @test */
	public function it_provides_rewrite_rules_for_a_given_request_method() {
		$rules = $this->get_route_collection()->get_rewrite_array_for_method( 'GET' );

		$this->assertSame( [
			'/add/([^/]+)' => 'index.php?add_param=$matches[1]',
			'^/get/([^/]+)$' => 'index.php?get_param=$matches[1]&matched_route=94e2c88860f0ee9488ad3af086598b18',
		], $rules );
	}

	/** @test */
	public function it_provides_query_vars_for_all_registered_routes() {
		$vars = $this->get_route_collection()->get_query_vars();

		$this->assertSame(
			[ 'add_param', 'get_param', 'matched_route', 'post_param', 'put_param' ],
			$vars
		);
	}
}
