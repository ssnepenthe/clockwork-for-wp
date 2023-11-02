<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork\Clockwork;
use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Clockwork_Provider;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Read_Only_Configuration;
use Clockwork_For_Wp\Storage_Factory;
use Clockwork_For_Wp\Tests\Creates_Config;
use Null_Storage_For_Tests;
use PHPUnit\Framework\TestCase;

class Plugin_Test extends TestCase {
	use Creates_Config;

	/** @test */
	public function it_passes_constructor_values_to_container(): void {
		$plugin = new Plugin( [], [ 'a' => 'b' ] );

		$this->assertEquals( 'b', $plugin->get_pimple()[ 'a' ] );
	}

	/** @test */
	public function it_can_filter_data_collection_using_except_uri_list(): void {
		$should_collect = $this->create_should_collect( [
			'requests' => [
				'except' => [
					'^clockwork',
					'^something',
					'^another',
					'a-specific-slug#with_hash',
				],
			],
		] );

		$this->assertTrue( $should_collect->filter( $this->create_request( 'blog/some-post-slug' ) ) );
		$this->assertTrue( $should_collect->filter( $this->create_request( 'blog/clockwork-rocks' ) ) );
		$this->assertTrue( $should_collect->filter( $this->create_request( 'blog/something-strange-happened' ) ) );
		$this->assertTrue( $should_collect->filter( $this->create_request( 'blog/another-list-post' ) ) );

		$this->assertFalse( $should_collect->filter( $this->create_request( 'clockwork/app' ) ) );
		$this->assertFalse( $should_collect->filter( $this->create_request( 'something-entirely-different' ) ) );
		$this->assertFalse( $should_collect->filter( $this->create_request( 'another-one-bites-the-dust' ) ) );
		$this->assertFalse( $should_collect->filter( $this->create_request( 'a-specific-slug#with_hash' ) ) );
	}

	/** @test */
	public function it_can_filter_data_collection_using_only_uri_list(): void {
		$should_collect = $this->create_should_collect( [
			'requests' => [
				'only' => [
					'^blog',
					'^a-specific-slug$',
					'#with_hash',
				],
			],
		] );

		$this->assertTrue( $should_collect->filter( $this->create_request( 'blog/some-post-slug' ) ) );
		$this->assertTrue( $should_collect->filter( $this->create_request( 'a-specific-slug' ) ) );
		$this->assertTrue( $should_collect->filter( $this->create_request( 'something#with_hash' ) ) );

		$this->assertFalse( $should_collect->filter( $this->create_request( 'clockwork/app' ) ) );
		$this->assertFalse( $should_collect->filter( $this->create_request( 'something-entirely-different' ) ) );
		$this->assertFalse( $should_collect->filter( $this->create_request( 'another-one-bites-the-dust' ) ) );
	}

	/** @test */
	public function it_can_filter_data_collection_for_preflight_requests(): void {
		$should_collect = fn( $except_preflight ) => $this->create_should_collect( [
			'requests' => [
				'except_preflight' => $except_preflight,
			],
		] );

		$this->assertFalse( $should_collect( true )->filter( $this->create_request( '/', 'OPTIONS' ) ) );
		$this->assertTrue( $should_collect( false )->filter( $this->create_request( '/', 'OPTIONS' ) ) );
	}

	private function create_plugin( array $user_config = [] ) {
		$global_config = [
			'register_helpers' => false,
			'storage' => [
				'driver' => 'null',
			],
		];

		$plugin = new Plugin( [], [
			Read_Only_Configuration::class => $this->create_config( $user_config + $global_config ),
		] );

		$plugin->register( new Clockwork_Provider() );

		$plugin->get_pimple()->extend(
			Storage_Factory::class,
			static fn( $factory ) => $factory->register_custom_factory(
				'null',
				static fn() => new Null_Storage_For_Tests()
			)
		);

		$plugin->lock();

		return $plugin;
	}

	private function create_request( $uri, $method = 'GET' ) {
		return new IncomingRequest( [
			'method' => $method,
			'uri' => $uri,
		] );
	}

	private function create_should_collect( array $user_config = [] ) {
		return $this->create_plugin( $user_config )->get_pimple()[ Clockwork::class ]->shouldCollect();
	}
}
