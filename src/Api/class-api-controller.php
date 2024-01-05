<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Api;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork_For_Wp\Is;
use Clockwork_For_Wp\Metadata;
use Clockwork_For_Wp\Request;
use Clockwork_For_Wp\Routing\Json_Responder;
use SimpleWpRouting\Exception\NotFoundHttpException;

use function Clockwork_For_Wp\array_only;

final class Api_Controller {
	private $authenticator;

	private $is;

	private $metadata;

	private $request;

	public function __construct( AuthenticatorInterface $authenticator, Metadata $metadata, Request $request, Is $is ) {
		$this->authenticator = $authenticator;
		$this->metadata = $metadata;
		$this->request = $request;
		$this->is = $is;
	}

	public function authenticate(): Json_Responder {
		$this->ensure_clockwork_is_enabled();

		$token = $this->authenticator->attempt(
			\array_filter( $this->extract_credentials() ) // @todo Filter necessary?
		);

		return new Json_Responder( [ 'token' => $token ], $token ? 200 : 403 );
	}

	public function serve_extended_json( array $params ): Json_Responder {
		return $this->serve_json( $params, true );
	}

	public function serve_json( array $params, bool $extended = false ): Json_Responder {
		$this->ensure_clockwork_is_enabled();

		$id = $params['id'];
		$direction = $params['direction'] ?? null;
		$count = $params['count'] ?? null;

		$authenticated = $this->authenticator->check( $this->request->get_header( 'X_CLOCKWORK_AUTH' ) );

		if ( true !== $authenticated ) {
			return new Json_Responder( [
				'message' => $authenticated,
				'requires' => $this->authenticator->requires(),
			], 403 );
		}

		if ( $extended ) {
			$data = $this->metadata->get_extended( $id, $direction, $count );
		} else {
			$data = $this->metadata->get( $id, $direction, $count );
		}

		$data = $this->apply_filters( $data );

		return new Json_Responder( $data );
	}

	/**
	 * @return Json_Responder|void
	 */
	public function update_data( array $params ) {
		$this->ensure_clockwork_is_enabled();

		$request = $this->metadata->get( $params['id'] );

		if ( ! $request ) {
			return new Json_Responder( [ 'message' => 'Request not found.' ], 404 );
		}

		$content = $this->request->json();
		$token = $content['_token'] ?? '';

		if ( ! $request->updateToken || ! \hash_equals( $request->updateToken, $token ) ) {
			return new Json_Responder( [ 'message' => 'Invalid update token.' ], 403 );
		}

		foreach ( array_only( $content, [ 'clientMetrics', 'webVitals' ] ) as $key => $value ) {
			$request->{$key} = $value;
		}

		$this->metadata->update( $request );
	}

	private function apply_filters( $data ) {
		$except = \array_filter( \explode( ',', $this->request->get_input( 'except', '' ) ) );
		$only = \array_filter( \explode( ',', $this->request->get_input( 'only', '' ) ) );

		$transformer = static function ( $request ) use ( $except, $only ) {
			return $only
				? $request->only( $only )
				: $request->except( \array_merge( $except, [ 'updateToken' ] ) );
		};

		if ( \is_array( $data ) ) {
			$data = \array_map( $transformer, $data );
		} elseif ( $data ) {
			$data = $transformer( $data );
		}

		return $data;
	}

	private function ensure_clockwork_is_enabled(): void {
		if ( ! $this->is->enabled() ) {
			throw new NotFoundHttpException();
		}
	}

	// @todo Move to route handler invoker?
	private function extract_credentials() {
		if ( ! $this->request->is_json() ) {
			// Clockwork as browser extension sends POST request as multipart/form-data.
			return [
				'username' => \filter_input( \INPUT_POST, 'username' ),
				'password' => \filter_input( \INPUT_POST, 'password' ),
			];
		}

		// @todo Verify this is still necessary after updating clockwork.
		// Clockwork as web-app sends POST request as application/json.
		$input = \file_get_contents( 'php://input' );
		$decoded = \json_decode( $input, true );

		if ( null === $decoded || \JSON_ERROR_NONE !== \json_last_error() ) {
			return [];
		}

		return [
			'username' => $decoded['username'] ?? null,
			'password' => $decoded['password'] ?? null,
		];
	}
}
