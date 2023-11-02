<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wp_Rewrite;
use PHPUnit\Framework\TestCase;

class Wp_Rewrite_Test extends TestCase {
	/**
	 * @test
	 */
	public function it_correctly_records_wp_rewrite_data(): void {
		$data_source = new Wp_Rewrite();
		$request = new Request();

		$data_source->set_structure( '/%year%/%monthnum%/%day%/%postname%/' );
		$data_source->set_trailing_slash( true );
		$data_source->set_front( '/' );
		$data_source->set_rules( [
			'page/?([0-9]{1,})/?$' => 'index.php?&paged=$matches[1]',
			'search/(.+)/?$' => 'index.php?s=$matches[1]',
		] );
		$data_source->add_rule( 'robots\.txt$', 'index.php?robots=1' );

		$data_source->resolve( $request );

		$data = $request->userData( 'Routing' )->toArray();

		$this->assertEquals( [
			[
				'Item' => 'Permalink Structure',
				'Value' => '/%year%/%monthnum%/%day%/%postname%/',
			],
			[
				'Item' => 'Trailing Slash?',
				'Value' => 'TRUE',
			],
			[
				'Item' => 'Rewrite Front',
				'Value' => '/',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Rewrite Settings',
			],
		], $data[0] );

		$this->assertEquals( [
			[
				'Regex' => 'page/?([0-9]{1,})/?$',
				'Query' => 'index.php?&paged=$matches[1]',
			],
			[
				'Regex' => 'search/(.+)/?$',
				'Query' => 'index.php?s=$matches[1]',
			],
			[
				'Regex' => 'robots\.txt$',
				'Query' => 'index.php?robots=1',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Rewrite Rules',
			],
		], $data[1] );
	}

	/**
	 * @test
	 */
	public function it_correctly_describes_trailing_slash_value(): void {
		$data_source = new Wp_Rewrite();
		$request = new Request();

		$data_source->set_trailing_slash( false );

		$data_source->resolve( $request );

		$this->assertEquals( 'FALSE', $request->userData( 'Routing' )->toArray()[0][1]['Value'] );

		$request = new Request();

		$data_source->set_trailing_slash( true );

		$data_source->resolve( $request );

		$this->assertEquals( 'TRUE', $request->userData( 'Routing' )->toArray()[0][1]['Value'] );
	}
}
