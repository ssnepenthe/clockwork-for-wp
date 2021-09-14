<?php

namespace Clockwork_For_Wp\Tests\Unit\Routing;

use PHPUnit\Framework\TestCase;

use function Clockwork_For_Wp\Routing\build_unencoded_query;

class Helpers_Test extends TestCase {
	public function test_build_unencoded_query() {
		$query = build_unencoded_query( [ 'a' => 'b', 'c' => 'd' ] );

		$this->assertSame( 'a=b&c=d', $query );

		$query = build_unencoded_query( [ 'matched_route' => '/test/([^/]+)' ] );

		// Non-alphanumeric characters should remain unchanged.
		$this->assertSame( 'matched_route=/test/([^/]+)', $query );
	}
}
