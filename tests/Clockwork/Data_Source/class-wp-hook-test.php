<?php

namespace Clockwork_For_Wp\Tests\Clockwork\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wp_Hook;
use PHPUnit\Framework\TestCase;

class Wp_Hook_Test extends TestCase {
	protected $data_source;
	protected $request;
	protected $request_is_resolved;

	protected function setUp() : void {
		$this->data_source = new Wp_Hook();
		$this->request = new Request();
		$this->request_is_resolved = false;
	}

	protected function tearDown() : void {
		$this->data_source = null;
		$this->request = null;
		$this->request_is_resolved = false;
	}

	protected function resolve_request() {
		if ( $this->request_is_resolved ) {
			return;
		}

		$this->data_source->resolve( $this->request );

		$this->request_is_resolved = true;
	}

	protected function user_data() {
		if ( ! $this->request_is_resolved ) {
			$this->resolve_request();
		}

		return $this->request->userData( 'Hooks' )->toArray();
	}

	/** @test */
	public function it_correctly_records_hook_data() {
		$this->data_source->add_hook( 'tag1' );
		$this->data_source->add_hook( 'tag2', 15 );
		$this->data_source->add_hook( 'tag3', 15, 'array_map' );
		$this->data_source->add_hook( 'tag4', 15, 'array_map', 3 );

		$this->assertEquals( [
			'Tag' => 'tag1',
			'Priority' => '',
			'Callback' => '',
			'Accepted Args' => '',
		] , $this->user_data()[0][0] );

		$this->assertEquals( [
			'Tag' => 'tag2',
			'Priority' => '15',
			'Callback' => '',
			'Accepted Args' => '',
		] , $this->user_data()[0][1] );

		$this->assertEquals( [
			'Tag' => 'tag3',
			'Priority' => '15',
			'Callback' => 'array_map()',
			'Accepted Args' => '',
		] , $this->user_data()[0][2] );

		$this->assertEquals( [
			'Tag' => 'tag4',
			'Priority' => '15',
			'Callback' => 'array_map()',
			'Accepted Args' => '3',
		] , $this->user_data()[0][3] );
	}

	/** @test */
	public function it_doesnt_create_the_userdata_entry_when_there_are_no_hooks() {
		$this->resolve_request();

		$this->assertEquals( [], $this->request->userData );
	}
}
