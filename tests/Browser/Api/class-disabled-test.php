<?php

namespace Clockwork_For_Wp\Tests\Browser\Api;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Disabled_Test extends Test_Case {
	protected function test_config(): array {
		return [
			'enable' => false,
		];
	}

	/** @test */
	public function it_returns_not_found_response_for_base_api_routes() {
		$this->get( '/__clockwork/latest' )
			->assert_not_found();
	}

	/** @test */
	public function it_returns_not_found_response_for_auth_api_routes() {
		$this->post( '/__clockwork/auth', [
			'doesnt' => 'really',
			'matter' => '...',
		] )
			->assert_not_found();
	}

	/** @test */
	public function it_returns_not_found_response_for_extended_api_routes() {
		$this->get( '/__clockwork/latest/extended' )
			->assert_not_found();
	}
}
