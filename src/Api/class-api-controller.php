<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Api;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork_For_Wp\Incoming_Request;
use Clockwork_For_Wp\Metadata;
use Clockwork_For_Wp\Plugin;
use ToyWpRouting\Exception\NotFoundHttpException;
use ToyWpRouting\Responder\JsonResponder;

use function Clockwork_For_Wp\array_only;

final class Api_Controller {
	private $authenticator;
	private $metadata;
	private $request;
	private $plugin;

	public function __construct(
		AuthenticatorInterface $authenticator,
		Metadata $metadata,
		Incoming_Request $request,
		Plugin $plugin
	) {
		$this->authenticator = $authenticator;
		$this->metadata = $metadata;
		$this->request = $request;
		$this->plugin = $plugin;
	}

	public function authenticate() {
		$this->assert_clockwork_is_enabled();

		$token = $this->authenticator->attempt(
			\array_filter( $this->extract_credentials() ) // @todo Filter necessary?
		);

		return ( new JsonResponder( [ 'token' => $token ], $token ? 200 : 403 ) )
			->json()
			->dontEnvelopeResponse();
	}

	public function serve_extended_json( $id ) {
		return $this->serve_json( $id, null, null, true );
	}

	public function serve_json( $id, $direction = null, $count = null, $extended = null ) {
		$this->assert_clockwork_is_enabled();

		// @todo Handle 404s.
		// @todo Is this really necessary?
		if ( null === $id ) {
			return; // @todo
		}

		$authenticated = $this->authenticator->check(
			// @todo Move to route handler invoker?
			$this->request->header( 'X_CLOCKWORK_AUTH' )
		);

		if ( true !== $authenticated ) {
			return ( new JsonResponder( [
				'message' => $authenticated,
				'requires' => $this->authenticator->requires(),
			], 403 ) )->json()->dontEnvelopeResponse();
		}

		if ( null !== $extended ) {
			$data = $this->metadata->get_extended( $id, $direction, $count );
		} else {
			$data = $this->metadata->get( $id, $direction, $count );
		}

		if ( ! $data ) {
			throw new NotFoundHttpException();
		}

		$data = $this->apply_filters( $data );

		return ( new JsonResponder( $data ) )->json()->dontEnvelopeResponse();
	}

	public function update_data( $id ) {
		$this->assert_clockwork_is_enabled();

		$request = $this->metadata->get( $id );

		if ( ! $request ) {
			return ( new JsonResponder( [ 'message' => 'Request not found' ], 404 ) )
				->json()
				->dontEnvelopeResponse();
		}

		$content = $this->request->json();
		$token = $content['_token'] ?? '';

		if ( ! $request->updateToken || ! \hash_equals( $request->updateToken, $token ) ) {
			return ( new JsonResponder( [ 'message' => 'Invalid update token' ], 403 ) )
				->json()
				->dontEnvelopeResponse();
		}

		foreach ( array_only( $content, [ 'clientMetrics', 'webVitals' ] ) as $key => $value ) {
			$request->{$key} = $value;
		}

		$this->metadata->update( $request );
	}

	private function apply_filters( $data ) {
		$except = isset( $this->request->input['except'] )
			? \explode( ',', $this->request->input['except'] )
			: [];
		$only = isset( $this->request->input['only'] )
			? \explode( ',', $this->request->input['only'] )
			: null;

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

	private function assert_clockwork_is_enabled(): void {
		if ( ! $this->plugin->is_enabled() ) {
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
