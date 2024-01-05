<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Request\IncomingRequest;
use SimpleWpRouting\Support\RequestContext;

final class Request {
	private IncomingRequest $incoming_request;

	private ?array $json = null;

	private RequestContext $request_context;

	public function __construct( IncomingRequest $incoming_request, RequestContext $request_context ) {
		$this->incoming_request = $incoming_request;
		$this->request_context = $request_context;
	}

	public function get_header( string $key, ?string $default = null ): ?string {
		return $this->request_context->getHeader( $key, $default );
	}

	public function get_incoming_request(): IncomingRequest {
		return $this->incoming_request;
	}

	public function get_input( string $key, ?string $default = null ): ?string {
		return $this->incoming_request->input[ $key ] ?? $default;
	}

	public function is_heartbeat() {
		return 'POST' === $this->incoming_request->method
			&& 'admin-ajax.php' === \mb_substr( $this->incoming_request->uri, -14 )
			&& 'heartbeat' === $this->get_input( 'action' );
	}

	public function is_json() {
		return 0 === \mb_strpos( $this->request_context->getHeader( 'CONTENT_TYPE', '' ), 'application/json' );
	}

	public function json() {
		if ( null === $this->json ) {
			$content = \file_get_contents( 'php://input' );
			$json = \json_decode( $content, true );

			$this->json = \is_array( $json ) ? $json : [];
		}

		return $this->json;
	}
}
