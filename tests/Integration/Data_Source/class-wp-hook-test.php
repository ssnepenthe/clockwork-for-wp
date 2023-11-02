<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Clockwork;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Data_Source_Factory;
use Clockwork_For_Wp\Data_Source\Wp_Hook;
use Clockwork_For_Wp\Incoming_Request;
use Clockwork_For_Wp\Is;
use Clockwork_For_Wp\Tests\Creates_Config;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class Wp_Hook_Test extends TestCase {
	use Creates_Config;

	/** @test */
	public function it_correctly_records_hook_data(): void {
		$data_source = new Wp_Hook();
		$request = new Request();

		$data_source->add_hook( 'tag1' );
		$data_source->add_hook( 'tag2', 15 );
		$data_source->add_hook( 'tag3', 15, 'array_map' );
		$data_source->add_hook( 'tag4', 15, 'array_map', 3 );

		$data_source->resolve( $request );

		$this->assertEquals( [
			'Tag' => 'tag1',
			'Priority' => '',
			'Callback' => '',
			'Accepted Args' => '',
		], $request->userData( 'Hooks' )->toArray()[0][0] );

		$this->assertEquals( [
			'Tag' => 'tag2',
			'Priority' => '15',
			'Callback' => '',
			'Accepted Args' => '',
		], $request->userData( 'Hooks' )->toArray()[0][1] );

		$this->assertEquals( [
			'Tag' => 'tag3',
			'Priority' => '15',
			'Callback' => 'array_map()',
			'Accepted Args' => '',
		], $request->userData( 'Hooks' )->toArray()[0][2] );

		$this->assertEquals( [
			'Tag' => 'tag4',
			'Priority' => '15',
			'Callback' => 'array_map()',
			'Accepted Args' => '3',
		], $request->userData( 'Hooks' )->toArray()[0][3] );
	}

	/** @test */
	public function it_can_be_configured_to_collect_all_hook_tags_except_a_subset(): void {
		$data_source = $this->create_data_source_via_factory( [
			'except_tags' => [ 'tag2', 'tag3_[\w]', '^tag4', '5$' ],
		] );
		$request = new Request();

		$data_source->add_hook( 'tag1' ); // yes
		$data_source->add_hook( 'tag1_xyz' ); // yes
		$data_source->add_hook( 'tag2' ); // no
		$data_source->add_hook( 'tag2_xyz' ); // no
		$data_source->add_hook( 'tag3' ); // yes
		$data_source->add_hook( 'tag3_xyz' ); // no
		$data_source->add_hook( 'tag4' ); // no
		$data_source->add_hook( 'tag4_xyz' ); // no
		$data_source->add_hook( 'tag5' ); // no
		$data_source->add_hook( 'tag5_xyz' ); // yes

		$data_source->resolve( $request );

		$prepared = \array_map(
			fn( $item ) => $item['Tag'],
			\array_filter(
				$request->userData( 'Hooks' )->toArray()[0],
				fn( $item ) => \array_key_exists( 'Tag', $item )
			)
		);

		$this->assertCount( 4, $prepared );
		$this->assertSame( [ 'tag1', 'tag1_xyz', 'tag3', 'tag5_xyz' ], $prepared );
	}

	/** @test */
	public function it_can_be_configured_to_only_collect_a_subset_of_hook_tags(): void {
		$data_source = $this->create_data_source_via_factory( [
			'only_tags' => [ 'tag2', 'tag3_[\w]', '^tag4', '5$' ],
		] );
		$request = new Request();

		$data_source->add_hook( 'tag1' ); // no
		$data_source->add_hook( 'tag1_xyz' ); // no
		$data_source->add_hook( 'tag2' ); // yes
		$data_source->add_hook( 'tag2_xyz' ); // yes
		$data_source->add_hook( 'tag3' ); // no
		$data_source->add_hook( 'tag3_xyz' ); // yes
		$data_source->add_hook( 'tag4' ); // yes
		$data_source->add_hook( 'tag4_xyz' ); // yes
		$data_source->add_hook( 'tag5' ); // yes
		$data_source->add_hook( 'tag5_xyz' ); // no

		$data_source->resolve( $request );

		$prepared = \array_map(
			fn( $item ) => $item['Tag'],
			\array_filter(
				$request->userData( 'Hooks' )->toArray()[0],
				fn( $item ) => \array_key_exists( 'Tag', $item )
			)
		);

		$this->assertCount( 6, $prepared );
		$this->assertSame(
			[ 'tag2', 'tag2_xyz', 'tag3_xyz', 'tag4', 'tag4_xyz', 'tag5' ],
			$prepared
		);
	}

	/** @test */
	public function it_favors_the_only_tags_configuration_over_the_except_tags_configuration(): void {
		$data_source = $this->create_data_source_via_factory( [
			'except_tags' => [ 'tag2' ],
			'only_tags' => [ 'tag2' ],
		] );
		$request = new Request();

		$data_source->add_hook( 'tag1' ); // no
		$data_source->add_hook( 'tag1_xyz' ); // no
		$data_source->add_hook( 'tag2' ); // yes
		$data_source->add_hook( 'tag2_xyz' ); // yes
		$data_source->add_hook( 'tag3' ); // no
		$data_source->add_hook( 'tag3_xyz' ); // yes
		$data_source->add_hook( 'tag4' ); // yes
		$data_source->add_hook( 'tag4_xyz' ); // yes
		$data_source->add_hook( 'tag5' ); // yes
		$data_source->add_hook( 'tag5_xyz' ); // no

		$data_source->resolve( $request );

		$prepared = \array_map(
			fn( $item ) => $item['Tag'],
			\array_filter(
				$request->userData( 'Hooks' )->toArray()[0],
				fn( $item ) => \array_key_exists( 'Tag', $item )
			)
		);

		$this->assertCount( 2, $prepared );
		$this->assertSame(
			[ 'tag2', 'tag2_xyz' ],
			$prepared
		);
	}

	/** @test */
	public function it_can_be_configured_to_collect_all_hooks_by_callback_except_a_subset(): void {
		$data_source = $this->create_data_source_via_factory( [
			'except_callbacks' => [ '^array_' ],
		] );
		$request = new Request();

		$data_source->add_hook( 'tag1', null, 'array_map' ); // no
		$data_source->add_hook( 'tag1_xyz', null, 'array_filter' ); // no
		$data_source->add_hook( 'tag2', null, 'str_repeat' ); // yes
		$data_source->add_hook( 'tag2_xyz', null, 'str_replace' ); // yes

		$data_source->resolve( $request );

		$prepared = \array_map(
			fn( $item ) => $item['Tag'],
			\array_filter(
				$request->userData( 'Hooks' )->toArray()[0],
				fn( $item ) => \array_key_exists( 'Tag', $item )
			)
		);

		$this->assertCount( 2, $prepared );
		$this->assertSame( [ 'tag2', 'tag2_xyz' ], $prepared );
	}

	/** @test */
	public function it_can_be_configured_to_only_collect_a_subset_of_hooks_by_callback(): void {
		$data_source = $this->create_data_source_via_factory( [
			'only_callbacks' => [ '^array_' ],
		] );
		$request = new Request();

		$data_source->add_hook( 'tag1', null, 'array_map' ); // yes
		$data_source->add_hook( 'tag1_xyz', null, 'array_filter' ); // yes
		$data_source->add_hook( 'tag2', null, 'str_repeat' ); // no
		$data_source->add_hook( 'tag2_xyz', null, 'str_replace' ); // no

		$data_source->resolve( $request );

		$prepared = \array_map(
			fn( $item ) => $item['Tag'],
			\array_filter(
				$request->userData( 'Hooks' )->toArray()[0],
				fn( $item ) => \array_key_exists( 'Tag', $item )
			)
		);

		$this->assertCount( 2, $prepared );
		$this->assertSame( [ 'tag1', 'tag1_xyz' ], $prepared );
	}

	/** @test */
	public function it_favors_the_only_callbacks_configuration_over_the_except_callbacks_configuration(): void {
		$data_source = $this->create_data_source_via_factory( [
			'except_callbacks' => [ '^array_' ],
			'only_callbacks' => [ '^array_' ],
		] );
		$request = new Request();

		$data_source->add_hook( 'tag1', null, 'array_map' ); // yes
		$data_source->add_hook( 'tag1_xyz', null, 'array_filter' ); // yes
		$data_source->add_hook( 'tag2', null, 'str_repeat' ); // no
		$data_source->add_hook( 'tag2_xyz', null, 'str_replace' ); // no

		$data_source->resolve( $request );

		$prepared = \array_map(
			fn( $item ) => $item['Tag'],
			\array_filter(
				$request->userData( 'Hooks' )->toArray()[0],
				fn( $item ) => \array_key_exists( 'Tag', $item )
			)
		);

		$this->assertCount( 2, $prepared );
		$this->assertSame( [ 'tag1', 'tag1_xyz' ], $prepared );
	}

	/** @test */
	public function it_doesnt_create_the_userdata_entry_when_there_are_no_hooks(): void {
		$data_source = new Wp_Hook();
		$request = new Request();

		$data_source->resolve( $request );

		$this->assertEquals( [], $request->userData );
	}

	private function create_data_source_via_factory( $filters = [] ) {
		$config = $this->create_config();
		$factory = new Data_Source_Factory(
			$config,
			new Is( $config, new Clockwork(), new Incoming_Request() ),
			new Container()
		);

		return $factory->create(
			'wp_hook',
			[
				'except_callbacks' => $filters['except_callbacks'] ?? [],
				'only_callbacks' => $filters['only_callbacks'] ?? [],
				'except_tags' => $filters['except_tags'] ?? [],
				'only_tags' => $filters['only_tags'] ?? [],
			]
		);
	}
}
