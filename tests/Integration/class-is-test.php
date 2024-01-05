<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork\Clockwork;
use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Is;
use Clockwork_For_Wp\Request;
use Clockwork_For_Wp\Tests\Creates_Config;
use PHPUnit\Framework\TestCase;
use SimpleWpRouting\Support\RequestContext;

class Is_Test extends TestCase {
	use Creates_Config;

	/**
	 * @test
	 */
	public function it_can_check_if_a_feature_is_enabled(): void {
		$is = $this->create_is( [
			'data_sources' => [
				'one' => [ 'enabled' => true ],
				'two' => [ 'enabled' => false ],
			],
		] );

		$this->assertTrue( $is->feature_enabled( 'one' ) );
		$this->assertFalse( $is->feature_enabled( 'two' ) );

		// False for features that are not registered.
		$this->assertFalse( $is->feature_enabled( 'three' ) );
	}

	/**
	 * @test
	 */
	public function it_can_check_if_clockwork_is_enabled(): void {
		// Default true.
		$is = $this->create_is( [] );

		$this->assertTrue( $is->enabled() );

		// Explicitly enabled.
		$is = $this->create_is( [ 'enable' => true ] );

		$this->assertTrue( $is->enabled() );

		// Explicitly disabled.
		$is = $this->create_is( [ 'enable' => false ] );

		$this->assertFalse( $is->enabled() );
	}

	/**
	 * @test
	 */
	public function it_can_check_if_web_app_is_enabled(): void {
		// Default true.
		$is = $this->create_is( [] );

		$this->assertTrue( $is->web_enabled() );

		// Enabled and web enabled.
		$is = $this->create_is( [ 'enable' => true, 'web' => true ] );

		$this->assertTrue( $is->web_enabled() );

		// Enabled, not web enabled.
		$is = $this->create_is( [ 'enable' => true, 'web' => false ] );

		$this->assertFalse( $is->web_enabled() );

		// Disabled, web enabled.
		$is = $this->create_is( [ 'enable' => false, 'web' => true ] );

		$this->assertFalse( $is->web_enabled() );
	}

	private function create_is( array $user_config ): Is {
		$config = $this->create_config( $user_config );
		$request = new Request( new IncomingRequest(), new RequestContext( 'GET', [] ) );

		return new Is( $config, new Clockwork(), $request );
	}
}
