<?php

namespace Clockwork_For_Wp\Tests\Browser\Frontend;

use Clockwork_For_Wp\Tests\Browser\Test_Case;

// @todo Nginx on VVV is currently blocking options requests and I am not ready to dig into this...
// For now we will just trust that the Clockwork ShouldCollect API is working as expected. For now
// it is roughly tested in our tests for the plugin class.
class Except_Preflight_Test extends Test_Case {
	// protected function test_config(): array {
	// 	return [
	// 		'requests' => [
	// 			'except_preflight' => false,
	// 		],
	// 	];
	// }

	// /** @test */
	// public function it_can_be_configured_to_send_clockwork_headers_for_options_requests() {
	// 	$this->request( 'OPTIONS', '/' )
	// 		->assert_header( 'x-clockwork-id' )
	// 		->assert_header( 'x-clockwork-version' );
	// }
}
