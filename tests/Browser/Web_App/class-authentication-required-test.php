<?php

namespace Clockwork_For_Wp\Tests\Browser\Web_App;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

class Authentication_Required_Test extends Test_Case {
	protected function test_config(): array {
		return [
			'authentication' => [
				'enabled' => true,
				'drivers' => [
					'simple' => [
						'config' => [
							'password' => static::PASSWORD,
						],
					],
				],
			],
		];
	}

	/** @test */
	public function it_is_locked_by_default() {
		// @todo This is going to require JavaScript... Is it even worth testing?
		// Currently having some issues with chrome driver within VVV.
		$this->markTestIncomplete( 'Not yet implemented' );
	}

	/** @test */
	public function it_unlocks_for_correct_credentials() {
		// @todo See above.
		$this->markTestIncomplete( 'Not yet implemented' );
	}
}
