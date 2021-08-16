<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork\Clockwork;
use Clockwork\Request\IncomingRequest;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Clockwork_Provider;
use Clockwork_For_Wp\Config;
use Clockwork_For_Wp\Plugin;
use Null_Storage_For_Tests;
use PHPUnit\Framework\TestCase;

class Plugin_Test extends TestCase {
	/** @test */
	public function it_passes_constructor_values_to_container() {
		$plugin = new Plugin( [], [ 'a' => 'b' ] );

		$this->assertEquals( 'b', $plugin['a'] );
	}

	/** @test */
	public function it_provides_access_to_config_object() {
		$plugin = new Plugin( [], [
			Config::class => new Config( [ 'a' => 'b' ] ),
		] );

		$this->assertEquals( 'b', $plugin->config( 'a' ) );
		$this->assertEquals( 'default', $plugin->config( 'b', 'default' ) );
	}

	/** @test */
	public function it_provides_a_list_of_enabled_data_sources() {
		$plugin = new Plugin( [], [
			Config::class => new Config( [
				'data_sources' => [
					'one' => [ 'enabled' => true ],
					'two' => [ 'enabled' => false ],
					'three' => [ 'enabled' => true ],
				],
			] ),
		] );

		$this->assertEquals( [
			'one' => [ 'enabled' => true ],
			'three' => [ 'enabled' => true ],
		], $plugin->get_enabled_data_sources() );
	}

	/** @test */
	public function it_can_check_if_a_feature_is_enabled() {
		$plugin = new Plugin( [], [
			Config::class => new Config( [
				'data_sources' => [
					'one' => [ 'enabled' => true ],
					'two' => [ 'enabled' => false ],
				],
			] ),
		] );

		$this->assertTrue( $plugin->is_feature_enabled( 'one' ) );
		$this->assertFalse( $plugin->is_feature_enabled( 'two' ) );

		// False for features that are not registered.
		$this->assertFalse( $plugin->is_feature_enabled( 'three' ) );
	}

	/** @test */
	public function it_can_check_if_clockwork_is_collecting_data() {
		$this->markTestSkipped(
			'Revisit this after implementing logic for collecting test and cli data.'
		);

		// Default true.
		$config = new Config( [] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertTrue( $plugin->is_collecting_data() );

		// Enabled and collecting data always.
		$config = new Config( [ 'enable' => true, 'collect_data_always' => true ] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertTrue( $plugin->is_collecting_data() );

		// Enabled, not collecting data always.
		$config = new Config( [ 'enable' => true, 'collect_data_always' => false ] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertTrue( $plugin->is_collecting_data() );

		// Disabled, collecting data always.
		$config = new Config( [ 'enable' => false, 'collect_data_always' => true ] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertTrue( $plugin->is_collecting_data() );

		// Disabled, not collecting data always.
		$config = new Config( [ 'enable' => false, 'collect_data_always' => false ] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertFalse( $plugin->is_collecting_data() );
	}

	/** @test */
	public function it_can_check_if_clockwork_is_enabled() {
		// Default true.
		$config = new Config( [] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertTrue( $plugin->is_enabled() );

		// Explicitly enabled.
		$config = new Config( [ 'enable' => true ] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertTrue( $plugin->is_enabled() );

		// Explicitly disabled.
		$config = new Config( [ 'enable' => false ] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertFalse( $plugin->is_enabled() );
	}

	/** @test */
	public function it_can_check_if_web_app_is_enabled() {
		// Default true.
		$config = new Config( [] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertTrue( $plugin->is_web_enabled() );

		// Enabled and web enabled.
		$config = new Config( [ 'enable' => true, 'web' => true ] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertTrue( $plugin->is_web_enabled() );

		// Enabled, not web enabled.
		$config = new Config( [ 'enable' => true, 'web' => false ] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertFalse( $plugin->is_web_enabled() );

		// Disabled, web enabled.
		$config = new Config( [ 'enable' => false, 'web' => true ] );
		$plugin = new Plugin( [], [ Config::class => $config ] );

		$this->assertFalse( $plugin->is_web_enabled() );
	}

	/** @test */
	public function it_can_filter_data_collection_using_except_uri_list() {
		$plugin = new Plugin( [], [
			Config::class => new Config( [
				'requests' => [
					'except' => [
						'^clockwork',
						'^something',
						'^another',
						'a-specific-slug#with_hash'
					],
				],
				'storage' => [
					'driver' => 'null',
					'drivers' => [
						'null' => [
							'class' => Null_Storage_For_Tests::class,
						],
					],
				],
				'register_helpers' => false,
			] ),
		] );
		$plugin[ Null_Storage_For_Tests::class ] = $plugin->protect( function() {
			return new Null_Storage_For_Tests();
		} );
		$plugin->register( new Clockwork_Provider( $plugin ) );
		$plugin->lock();

		$request = function( $uri ) {
			return new IncomingRequest( [
				'method' => 'GET',
				'uri' => $uri,
			] );
		};

		$should_collect = $plugin[ Clockwork::class ]->shouldCollect();

		$this->assertTrue( $should_collect->filter( $request( 'blog/some-post-slug' ) ) );
		$this->assertTrue( $should_collect->filter( $request( 'blog/clockwork-rocks' ) ) );
		$this->assertTrue( $should_collect->filter( $request( 'blog/something-strange-happened' ) ) );
		$this->assertTrue( $should_collect->filter( $request( 'blog/another-list-post' ) ) );

		$this->assertFalse( $should_collect->filter( $request( 'clockwork/app' ) ) );
		$this->assertFalse( $should_collect->filter( $request( 'something-entirely-different' ) ) );
		$this->assertFalse( $should_collect->filter( $request( 'another-one-bites-the-dust' ) ) );
		$this->assertFalse( $should_collect->filter( $request( 'a-specific-slug#with_hash' ) ) );
	}

	/** @test */
	public function it_can_filter_data_collection_using_only_uri_list() {
		$plugin = new Plugin( [], [
			Config::class => new Config( [
				'requests' => [
					'only' => [
						'^blog',
						'^a-specific-slug$',
						'#with_hash'
					],
				],
				'storage' => [
					'driver' => 'null',
					'drivers' => [
						'null' => [
							'class' => Null_Storage_For_Tests::class,
						],
					],
				],
				'register_helpers' => false,
			] ),
		] );
		$plugin[ Null_Storage_For_Tests::class ] = $plugin->protect( function() {
			return new Null_Storage_For_Tests();
		} );
		$plugin->register( new Clockwork_Provider( $plugin ) );
		$plugin->lock();

		$request = function( $uri ) {
			return new IncomingRequest( [
				'method' => 'GET',
				'uri' => $uri,
			] );
		};

		$should_collect = $plugin[ Clockwork::class ]->shouldCollect();

		$this->assertTrue( $should_collect->filter( $request( 'blog/some-post-slug' ) ) );
		$this->assertTrue( $should_collect->filter( $request( 'a-specific-slug' ) ) );
		$this->assertTrue( $should_collect->filter( $request( 'something#with_hash' ) ) );

		$this->assertFalse( $should_collect->filter( $request( 'clockwork/app' ) ) );
		$this->assertFalse( $should_collect->filter( $request( 'something-entirely-different' ) ) );
		$this->assertFalse( $should_collect->filter( $request( 'another-one-bites-the-dust' ) ) );
	}

	/** @test */
	public function it_can_filter_data_collection_for_preflight_requests() {
		$should_collect = function( $except_preflight ) {
			$plugin = new Plugin( [], [
				Config::class => new Config( [
					'requests' => [
						'except_preflight' => $except_preflight,
					],
					'storage' => [
						'driver' => 'null',
						'drivers' => [
							'null' => [
								'class' => Null_Storage_For_Tests::class,
							],
						],
					],
					'register_helpers' => false,
				] ),
			] );
			$plugin[ Null_Storage_For_Tests::class ] = $plugin->protect( function() {
				return new Null_Storage_For_Tests();
			} );
			$plugin->register( new Clockwork_Provider( $plugin ) );
			$plugin->lock();

			return $plugin[ Clockwork::class ]->shouldCollect();
		};

		$request = function( $uri ) {
			return new IncomingRequest( [
				'method' => 'OPTIONS',
				'uri' => $uri,
			] );
		};

		$this->assertFalse( $should_collect( true )->filter( $request( '/' ) ) );
		$this->assertTrue( $should_collect( false )->filter( $request( '/' ) ) );
	}
}
