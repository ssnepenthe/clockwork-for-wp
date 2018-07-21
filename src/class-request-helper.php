<?php

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;

class Request_Helper {
	protected $clockwork;

	public function __construct( Clockwork $clockwork ) {
		$this->clockwork = $clockwork;
	}

	public function finalize_request() {
		// @todo Verify data should actually be collected for current request.

		$this->clockwork->resolveRequest();
        $this->clockwork->storeRequest();
	}

	public function send_headers() {
		// @todo Verify headers should be set for this request.
    	if ( headers_sent() ) {
            return; // This shouldn't happen...
        }

        // @todo Set clockwork path?

        // @todo Any reason to suppress errors?
        // @todo Request as a direct dependency?
        header( 'X-Clockwork-Id: ' . $this->clockwork->getRequest()->id );
        header( 'X-Clockwork-Version: ' . Clockwork::VERSION );

        // @todo Handle custom headers.
        // @todo Handle server timing headers.
	}
}
