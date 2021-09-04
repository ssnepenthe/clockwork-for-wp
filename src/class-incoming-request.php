<?php

namespace Clockwork_For_Wp;

use Clockwork\Request\IncomingRequest;

class Incoming_Request extends IncomingRequest {
	protected $ajax_uri;
	protected $content;
	protected $headers = [];
	protected $json;

	public function content() {
		if ( null === $this->content ) {
			$this->content = file_get_contents( 'php://input' );
		}

		return $this->content;
	}

	public function header( $key, $default = null ) {
		$key = strtolower( str_replace( '_', '-', $key ) );
		$header = $this->headers[ $key ] ?? null;

		return null !== $header ? $header : $default;
	}

	public function intended_method() {
		// Placeholder in case we ever need to support other methods.
		return $this->is_put() ? 'PUT' : $this->method;
	}

	public function is_heartbeat() {
		return 'POST' === $this->method
			&& $this->ajax_uri === $this->uri
			&& array_key_exists( 'action', $this->input )
			&& 'heartbeat' === $this->input['action'];
	}

	public function is_put() {
		$method = strtoupper( $this->method );

		if ( 'PUT' === $method ) {
			return true;
		}

		$override = $this->header( 'X-HTTP-METHOD-OVERRIDE' );

		if ( ! is_string( $override ) ) {
			return false;
		}

		$override = strtoupper( $override );

		return 'POST' === $method && 'PUT' === $override;
	}

	public function json() {
		if ( null === $this->json ) {
			$this->json = json_decode( $this->content(), true );
		}

		return $this->json;
	}

	public static function extract_headers( $server ) {
		$headers = [];

		foreach ( $server as $key => $value ) {
			if ( 0 === strpos( $key, 'HTTP_' ) ) {
				$key = substr( $key, 5 );
			} else if (
				in_array( $key, [ 'CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5' ], true )
			) {
				// $key = $key;
			} else {
				continue;
			}

			$key = strtolower( str_replace( '_', '-', $key ) );

			$headers[ $key ] = $value;
		}

		return $headers;
	}

	public static function from_globals() {

		return new static( [
			'ajax_uri' => parse_url( admin_url( 'admin-ajax.php' ), PHP_URL_PATH ),
			'cookies' => $_COOKIE,
			'headers' => static::extract_headers( $_SERVER ),
			'input' => $_REQUEST,
			'method' => $_SERVER['REQUEST_METHOD'],
			'uri' => $_SERVER['REQUEST_URI'],
		] );
	}
}