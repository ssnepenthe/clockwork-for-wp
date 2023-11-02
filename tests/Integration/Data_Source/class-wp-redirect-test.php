<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Wp_Redirect;
use PHPUnit\Framework\TestCase;

class Wp_Redirect_Test extends TestCase {
	/** @test */
	public function it_does_nothing_by_default(): void {
		$data_source = new Wp_Redirect();
		$request = new Request();

		$data_source->resolve( $request );

		$this->assertCount( 0, $request->log()->messages );
	}

	/** @test */
	public function it_logs_redirect_data_when_initial_location_is_set(): void {
		$data_source = new Wp_Redirect();

		$data_source->set_initial( 'location', 'test-location' );

		$request = new Request();

		$data_source->resolve( $request );

		$this->assertCount( 1, $request->log()->messages );

		$message = $request->log()->messages[0];

		$this->assertSame( 'test-location', $message['context']['Args']['location'] );
		$this->assertSame( 302, $message['context']['Args']['status'] ); // default
		$this->assertSame( 'WordPress', $message['context']['Args']['x-redirect-by'] ); // default
	}

	/** @test */
	public function it_allows_all_initial_and_filtered_args_to_be_set(): void {
		$data_source = new Wp_Redirect();

		$data_source->set_initial( 'location', 'initiallocation' );
		$data_source->set_initial( 'status', 301 );
		$data_source->set_initial( 'x-redirect-by', 'initialredirectby' );

		$data_source->set_filtered( 'location', 'filteredlocation' );
		$data_source->set_filtered( 'status', 307 );
		$data_source->set_filtered( 'x-redirect-by', 'filteredredirectby' );

		$request = new Request();

		$data_source->resolve( $request );

		$message = $request->log()->messages[0];

		$this->assertSame( 'initiallocation', $message['context']['Args']['location'] );
		$this->assertSame( 301, $message['context']['Args']['status'] );
		$this->assertSame( 'initialredirectby', $message['context']['Args']['x-redirect-by'] );

		$this->assertSame( 'filteredlocation', $message['context']['Filtered Args']['location'] );
		$this->assertSame( 307, $message['context']['Filtered Args']['status'] );
		$this->assertSame(
			'filteredredirectby',
			$message['context']['Filtered Args']['x-redirect-by']
		);
	}

	/** @test */
	public function it_omits_filtered_args_when_no_changes_have_been_made(): void {
		$data_source = new Wp_Redirect();

		$data_source->set_initial( 'location', 'initiallocation' );
		$data_source->set_initial( 'status', 302 );
		$data_source->set_initial( 'x-redirect-by', 'initialredirectby' );

		// Unchanged.
		$data_source->set_filtered( 'location', 'initiallocation' );
		$data_source->set_filtered( 'status', 302 );
		$data_source->set_filtered( 'x-redirect-by', 'initialredirectby' );

		$request = new Request();

		$data_source->resolve( $request );

		$message = $request->log()->messages[0];

		$this->assertArrayNotHasKey( 'Filtered Args', $message['context'] );
	}

	/** @test */
	public function it_correctly_identifies_differences_between_initial_and_filtered_args(): void {
		$data_source = new Wp_Redirect();

		$data_source->set_initial( 'location', 'initiallocation' );
		$data_source->set_initial( 'status', 302 );
		$data_source->set_initial( 'x-redirect-by', 'initialredirectby' );

		// Unchanged.
		$data_source->set_filtered( 'location', 'filteredlocation' ); // different
		$data_source->set_filtered( 'status', 301 ); // different
		$data_source->set_filtered( 'x-redirect-by', 'initialredirectby' ); // same

		$request = new Request();

		$data_source->resolve( $request );

		$filtered_args = $request->log()->messages[0]['context']['Filtered Args'];

		$this->assertArrayHasKey( 'location', $filtered_args );
		$this->assertSame( 'filteredlocation', $filtered_args['location'] );

		$this->assertArrayHasKey( 'status', $filtered_args );
		$this->assertSame( 301, $filtered_args['status'] );

		$this->assertArrayNotHasKey( 'x-redirect-by', $filtered_args );
	}

	/** @test */
	public function it_can_detect_when_wp_redirect_is_successful(): void {
		$data_source = new Wp_Redirect();

		$data_source->set_initial( 'location', 'test-location' );
		$data_source->finalize_wp_redirect_call();

		$request = new Request();

		$data_source->resolve( $request );

		$this->assertSame( 'Call to "wp_redirect"', $request->log()->messages[0]['message'] );
	}

	/** @test */
	public function it_can_detect_when_wp_redirect_call_bails(): void {
		// Falsy filtered location.
		$data_source = new Wp_Redirect();

		$data_source->set_initial( 'location', 'test-location' );

		$request = new Request();

		$data_source->resolve( $request );

		$this->assertSame(
			'Call to "wp_redirect" returned without redirecting user: "wp_redirect" filter returned a falsy value',
			$request->log()->messages[0]['message']
		);

		// Invalid status code.
		$data_source = new Wp_Redirect();

		$data_source->set_initial( 'location', 'test-location' );
		$data_source->set_filtered( 'location', 'test-location' );
		$data_source->set_filtered( 'status', 404 );

		$request = new Request();

		$data_source->resolve( $request );

		$this->assertSame(
			'Call to "wp_redirect" caused call to "wp_die": "wp_redirect_status" filter returned an invalid status code',
			$request->log()->messages[0]['message']
		);

		// Fallback
		$data_source = new Wp_Redirect();

		$data_source->set_initial( 'location', 'test-location' );
		$data_source->set_filtered( 'location', 'test-location' );
		$data_source->set_filtered( 'status', 302 );

		$request = new Request();

		$data_source->resolve( $request );

		$this->assertSame(
			'Call to "wp_redirect" appears to have bailed early: Reason unknown',
			$request->log()->messages[0]['message']
		);
	}
}
