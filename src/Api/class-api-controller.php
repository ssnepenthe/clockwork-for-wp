<?php

namespace Clockwork_For_Wp\Api;

use Clockwork\Clockwork;
use Clockwork\Request\IncomingRequest;
use Clockwork\Storage\Search;

class Api_Controller {
	protected $clockwork;
	protected $request;

	public function __construct( Clockwork $clockwork, IncomingRequest $request ) {
		$this->clockwork = $clockwork;
		$this->request = $request;
	}

	// @todo Authenticator directly?
	public function authenticate() {
		$token = $this->clockwork
			->getAuthenticator()
			->attempt( array_filter( $this->extract_credentials() ) ); // @todo Filter necessary?

		wp_send_json( [ 'token' => $token ], $token ? 200 : 403 );
	}

	public function serve_json( $id, $direction = null, $count = null, $extended = null ) {
		// @todo Handle 404s.
		// @todo Is this really necessary?
		if ( null === $id ) {
			return; // @todo
		}

		$authenticator = $this->clockwork->getAuthenticator();
		$authenticated = $authenticator->check(
			// @todo Move to route handler invoker?
			isset( $_SERVER['HTTP_X_CLOCKWORK_AUTH'] ) ? $_SERVER['HTTP_X_CLOCKWORK_AUTH'] : ''
		);

		if ( $authenticated !== true ) {
			status_header( 403 );

			wp_send_json( [
				'message' => $authenticated,
				'requires' => $authenticator->requires(),
			] );
		}

		if ( 'previous' !== $direction && 'next' !== $direction ) {
			$direction = null;
		}

		if ( null !== $count ) {
			$count = (int) $count;
		}

		if ( null !== $extended ) {
			$extended = true;
		}

		$filter = array_filter( $this->request->input, function( $key ) {
			return 'only' === $key || 'except' === $key;
		}, ARRAY_FILTER_USE_KEY );

		$data = $this->get_data( $id, $direction, $count, $filter, $extended );

		wp_send_json( $data ); // @todo
	}

	protected function get_data(
		$id = null,
		$direction = null,
		$count = null,
		$filter = [],
		$extended = null
	) {
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

		$except = isset( $filter['except'] ) ? explode( ',', $filter['except'] ) : [];
		$only = isset( $filter['only'] ) ? explode( ',', $filter['only'] ) : null;

		$transformer = function ( $request ) use ( $except, $only ) {
			return $only
				? $request->only( $only )
				: $request->except( array_merge( $except, [ 'updateToken' ] ) );
		};

		if ( is_array( $data ) ) {
			$data = array_map( $transformer, $data );
		} elseif ( $data ) {
			$data = $transformer( $data );
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

	// @todo Move to route handler invoker?
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
