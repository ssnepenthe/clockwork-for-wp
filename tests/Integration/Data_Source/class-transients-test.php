<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Transients;
use PHPUnit\Framework\TestCase;

class Transients_Test extends TestCase {
	protected $data_source;
	protected $request;
	protected $request_is_resolved;

	protected function setUp(): void {
		$this->data_source = new Transients();
		$this->request = new Request();
		$this->request_is_resolved = false;
	}

	protected function tearDown(): void {
		$this->data_source = null;
		$this->request = null;
		$this->request_is_resolved = false;
	}

	private function resolve_request(): void {
		if ( $this->request_is_resolved ) {
			return;
		}

		$this->data_source->resolve( $this->request );

		$this->request_is_resolved = true;
	}

	private function user_data() {
		if ( ! $this->request_is_resolved ) {
			$this->resolve_request();
		}

		return $this->request->userData( 'Caching' )->toArray();
	}

	/** @test */
	public function it_correctly_records_transients_data(): void {
		// Null values should be correctly removed.
		$this->data_source->setted( 'key1' );
		$this->data_source->setted( 'key2', 'value2' );
		$this->data_source->setted( 'key3', 'value3', $exp3 = 123 );
		$this->data_source->setted( 'key4', 'value4', $exp4 = 456, $is_site = true );

		$this->data_source->deleted( 'key5' );
		$this->data_source->deleted( 'key6', $is_site = true );

		$this->resolve_request();

		$setted_data = $this->user_data()[0];
		$deleted_data = $this->user_data()[1];

		$this->assertEquals( 'Setted Transients', $setted_data['__meta']['title'] );

		$this->assertEquals( [
			'Type' => 'setted',
			'Key' => 'key1',
			'Is Site' => 'No',
		], $setted_data[0] );

		$this->assertEquals( [
			'Type' => 'setted',
			'Key' => 'key2',
			'Value' => 'value2',
			'Is Site' => 'No',
			'Size' => 6,
		], $setted_data[1] );

		$this->assertEquals( [
			'Type' => 'setted',
			'Key' => 'key3',
			'Value' => 'value3',
			'Expiration' => 123,
			'Is Site' => 'No',
			'Size' => 6,
		], $setted_data[2] );

		$this->assertEquals( [
			'Type' => 'setted',
			'Key' => 'key4',
			'Value' => 'value4',
			'Expiration' => 456,
			'Is Site' => 'Yes',
			'Size' => 6,
		], $setted_data[3] );

		$this->assertEquals( 'Deleted Transients', $deleted_data['__meta']['title'] );

		$this->assertEquals( [
			'Type' => 'deleted',
			'Key' => 'key5',
			'Is Site' => 'No',
		], $deleted_data[0] );

		$this->assertEquals( [
			'Type' => 'deleted',
			'Key' => 'key6',
			'Is Site' => 'Yes',
		], $deleted_data[1] );
	}

	/** @test */
	public function it_doesnt_create_the_userdata_entry_when_there_are_no_transients(): void {
		$this->resolve_request();

		$this->assertEquals( [], $this->request->userData );
	}
}
