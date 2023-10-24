<?php

namespace Clockwork_For_Wp\Tests\Integration;

use Clockwork\Clockwork;
use Clockwork\Request\IncomingRequest;
use Clockwork_For_Wp\Clockwork_Provider;
use Clockwork_For_Wp\Incoming_Request;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Storage_Factory;
use Clockwork_For_Wp\Tests\Creates_Config;
use League\Config\Configuration;
use League\Config\ConfigurationInterface;
use Nette\Schema\Expect;
use Null_Storage_For_Tests;
use PHPUnit\Framework\TestCase;

class Plugin_Test extends TestCase {
	use Creates_Config;

	/** @test */
	public function it_passes_constructor_values_to_container() {
		$plugin = new Plugin( [], [ 'a' => 'b' ] );

		$this->assertEquals( 'b', $plugin->get_pimple()[ 'a' ] );
	}

	/** @test */
	public function it_provides_access_to_config_object() {
		$config = new Configuration( [ 'a' => Expect::string() ] );
		$config->merge( [ 'a' => 'b' ] );
		$plugin = new Plugin( [], [
			ConfigurationInterface::class => $config,
		] );

		$this->assertEquals( 'b', $plugin->config( 'a' ) );
		$this->assertEquals( 'default', $plugin->config( 'b', 'default' ) );
	}

	/** @test */
	public function it_can_filter_data_collection_using_except_uri_list() {
		$plugin = $this->create_plugin_with_configuration( [
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
			],
			'register_helpers' => false,
		] );
		$this->add_null_storage_to_storage_factory_on_plugin( $plugin );
		$plugin->lock();

		$request = function( $uri ) {
			return new IncomingRequest( [
				'method' => 'GET',
				'uri' => $uri,
			] );
		};

		$should_collect = $plugin->get_pimple()[ Clockwork::class ]->shouldCollect();

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
		$plugin = $this->create_plugin_with_configuration( [
			'requests' => [
				'only' => [
					'^blog',
					'^a-specific-slug$',
					'#with_hash'
				],
			],
			'storage' => [
				'driver' => 'null',
			],
			'register_helpers' => false,
		] );
		$this->add_null_storage_to_storage_factory_on_plugin( $plugin );
		$plugin->lock();

		$request = function( $uri ) {
			return new IncomingRequest( [
				'method' => 'GET',
				'uri' => $uri,
			] );
		};

		$should_collect = $plugin->get_pimple()[ Clockwork::class ]->shouldCollect();

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
			$plugin = $this->create_plugin_with_configuration( [
				'requests' => [
					'except_preflight' => $except_preflight,
				],
				'storage' => [
					'driver' => 'null',
				],
				'register_helpers' => false,
			] );
			$this->add_null_storage_to_storage_factory_on_plugin( $plugin );
			$plugin->lock();

			return $plugin->get_pimple()[ Clockwork::class ]->shouldCollect();
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

	private function create_plugin_with_configuration( array $user_config = [] ) {
		$plugin = new Plugin( [], [
			ConfigurationInterface::class => $this->create_config( $user_config ),
		] );

		return $plugin;
	}

	private function add_null_storage_to_storage_factory_on_plugin( $plugin ) {
		$plugin->register( new Clockwork_Provider( $plugin ) );

		$plugin->get_pimple()->extend( Storage_Factory::class, function( $factory ) {
			$factory->register_custom_factory( 'null', function() {
				return new Null_Storage_For_Tests();
			} );

			return $factory;
		} );
	}
}
