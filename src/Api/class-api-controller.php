<?php

namespace Clockwork_For_Wp\Api;

use Clockwork\Clockwork;
use Clockwork\Storage\Search;

class Api_Controller {
	protected $clockwork;

	public function __construct( Clockwork $clockwork ) {
		$this->clockwork = $clockwork;
	}

	// @todo Authenticator directly?
	public function authenticate() {
		$token = $this->clockwork
			->getAuthenticator()
			->attempt( array_filter( $this->extract_credentials() ) ); // @todo Filter necessary?

		wp_send_json( [ 'token' => $token ], $token ? 200 : 403 );
	}

	public function serve_json(
		$cfw_id,
		$cfw_direction = null,
		$cfw_count = null,
		$cfw_extended = null
	) {
		// @todo Handle 404s.
		// @todo Is this really necessary?
		if ( null === $cfw_id ) {
			return; // @todo
		}

		$authenticator = $this->clockwork->getAuthenticator();
		$authenticated = $authenticator->check(
			isset( $_SERVER['HTTP_X_CLOCKWORK_AUTH'] ) ? $_SERVER['HTTP_X_CLOCKWORK_AUTH'] : ''
		);

		if ( $authenticated !== true ) {
			status_header( 403 );

			wp_send_json( [
				'message' => $authenticated,
				'requires' => $authenticator->requires(),
			] );
		}

		if ( 'previous' !== $cfw_direction && 'next' !== $cfw_direction ) {
			$cfw_direction = null;
		}

		if ( null !== $cfw_count ) {
			$cfw_count = (int) $cfw_count;
		}

		if ( null !== $cfw_extended ) {
			$cfw_extended = true;
		}

		$data = $this->get_data( $cfw_id, $cfw_direction, $cfw_count, $cfw_extended );

		wp_send_json( $data ); // @todo
	}

	protected function get_data( $id = null, $direction = null, $count = null, $extended = null ) {
		$storage = $this->clockwork->getStorage();

		if ( 'previous' === $direction ) {
			$data = $storage->previous( $id, $count, Search::fromRequest( $_GET ) );
		} elseif ( 'next' === $direction ) {
			$data = $storage->next( $id, $count, Search::fromRequest( $_GET ) );
		} elseif ( 'latest' === $id ) {
			$data = $storage->latest( Search::fromRequest( $_GET ) );
		} else {
			$data = $storage->find( $id );
		}

		if ( $extended ) {
			$this->clockwork->extendRequest( $data );
		}

		return $data;
	}

	protected function is_json_request() {
		$content_type = '';

		if ( isset( $_SERVER['HTTP_CONTENT_TYPE'] ) ) {
			$content_type = $_SERVER['HTTP_CONTENT_TYPE'];
		} elseif ( isset( $_SERVER['CONTENT_TYPE'] ) ) {
			$content_type = $_SERVER['CONTENT_TYPE'];
		}

		return 0 === strpos( $content_type, 'application/json' );
	}

	protected function extract_credentials() {
		if ( ! $this->is_json_request() ) {
			// Clockwork as browser extension sends POST request as multipart/form-data.
			return [
				'username' => filter_input( INPUT_POST, 'username' ),
				'password' => filter_input( INPUT_POST, 'password' ),
			];
		}

		// @todo Verify this is still necessary after updating clockwork.
		// Clockwork as web-app sends POST request as application/json.
		$input = file_get_contents( 'php://input' );
		$decoded = json_decode( $input, true );

		if ( null === $decoded || JSON_ERROR_NONE !== json_last_error() ) {
			return [];
		}

		return [
			'username' => isset( $decoded['username'] ) ? $decoded['username'] : null,
			'password' => isset( $decoded['password'] ) ? $decoded['password'] : null,
		];
	}
}
