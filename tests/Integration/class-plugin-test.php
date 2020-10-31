<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork_For_Wp\Config;
use Clockwork_For_Wp\Plugin;
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
	public function it_can_check_if_a_uri_is_filtered() {
		$plugin = new Plugin( [], [
			Config::class => new Config( [
				'filter_uris' => [
					'^clockwork',
					'^something',
					'^another',
					'a-specific-slug#with_hash'
				],
			] ),
		] );

		$this->assertFalse( $plugin->is_uri_filtered( 'blog/some-post-slug' ) );
		$this->assertFalse( $plugin->is_uri_filtered( 'blog/clockwork-rocks' ) );
		$this->assertFalse( $plugin->is_uri_filtered( 'blog/something-strange-happened' ) );
		$this->assertFalse( $plugin->is_uri_filtered( 'blog/another-list-post' ) );

		$this->assertTrue( $plugin->is_uri_filtered( 'clockwork/app' ) );
		$this->assertTrue( $plugin->is_uri_filtered( 'something-entirely-different' ) );
		$this->assertTrue( $plugin->is_uri_filtered( 'another-one-bites-the-dust' ) );
		$this->assertTrue( $plugin->is_uri_filtered( 'a-specific-slug#with_hash' ) );
	}

	/** @test */
	public function it_can_check_if_clockwork_is_collecting_data() {
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
}
