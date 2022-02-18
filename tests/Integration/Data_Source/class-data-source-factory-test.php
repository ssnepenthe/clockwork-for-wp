<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork_For_Wp\Config;
use Clockwork_For_Wp\Data_Source;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Plugin;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class Data_Source_Factory_Test extends TestCase {
	/** @dataProvider provide_test_create */
	public function test_create( $name, $class ) {
		$factory = $this->create_factory();

		$this->assertInstanceOf( $class, $factory->create( $name ) );
	}

	public function test_create_caches_instances() {
		$factory = $this->create_factory();

		$this->assertSame( $factory->create( 'theme' ), $factory->create( 'theme' ) );
	}

	public function test_create_with_custom_factory() {
		$data_source = new class extends DataSource {};

		$factory = $this->create_factory();
		$factory->register_custom_factory( 'test', function() use ( $data_source ) {
			return $data_source;
		} );

		$this->assertSame( $data_source, $factory->create( 'test' ) );
	}

	public function test_create_with_custom_factory_override_for_built_in_data_source() {
		$data_source = new class extends DataSource {
			public function test() { return 'it works'; }
		};

		$factory = $this->create_factory();
		$factory->register_custom_factory( 'theme', function() use ( $data_source ) {
			return $data_source;
		} );

		$this->assertTrue( method_exists( $factory->create( 'theme' ), 'test' ) );
		$this->assertSame( 'it works', $factory->create( 'theme' )->test() );
	}

	public function test_create_unsupported_data_source() {
		$this->expectException( InvalidArgumentException::class );

		$factory = $this->create_factory();
		$factory->create( 'not-registered' );
	}

	public function test_get_enabled_data_sources() {
		$factory = $this->create_factory();

		$this->assertEquals(
			[ $factory->create( 'rest_api' ), $factory->create( 'transients' ) ],
			$factory->get_enabled_data_sources()
		);
	}

	public function provide_test_create() {
		yield [ 'conditionals', Data_Source\Conditionals::class ];
		yield [ 'constants', Data_Source\Constants::class ];
		yield [ 'core', Data_Source\Core::class ];
		yield [ 'errors', Data_Source\Errors::class ];
		// @todo Move patterns to config
		// yield [ 'php', Data_Source\Php::class ];
		yield [ 'rest_api', Data_Source\Rest_Api::class ];
		yield [ 'theme', Data_Source\Theme::class ];
		yield [ 'transients', Data_Source\Transients::class ];
		yield [ 'wp_hook', Data_Source\Wp_Hook::class ];
		yield [ 'wp_http', Data_Source\Wp_Http::class ];
		yield [ 'wp_mail', Data_Source\Wp_Mail::class ];
		yield [ 'wp_object_cache', Data_Source\Wp_Object_Cache::class ];
		yield [ 'wp_query', Data_Source\Wp_Query::class ];
		yield [ 'wp_redirect', Data_Source\Wp_Redirect::class ];
		yield [ 'wp_rewrite', Data_Source\Wp_Rewrite::class ];
		yield [ 'wp', Data_Source\Wp::class ];
		yield [ 'wpdb', Data_Source\Wpdb::class ];
		yield [ 'xdebug', Data_Source\Xdebug::class ];
	}

	protected function create_factory() {
		return new Data_Source\Data_Source_Factory( new Plugin( [], [
			'dir' => 'irrelevant',
			'wp_version' => 'irrelevant',
			'timestart' => 'irrelevant',
			Config::class => new Config( [
				'data_sources' => [
					'rest_api' => [ 'enabled' => true ],
					'theme' => [ 'enabled' => false ],
					'transients' => [ 'enabled' => true ],
				],
			] ),
			Event_Manager::class => new class {
				public function trigger() { /** irrelevant */ }
			}
		] ) );
	}
}
