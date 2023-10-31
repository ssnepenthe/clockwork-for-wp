<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Api;

use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork_For_Wp\Incoming_Request;
use Clockwork_For_Wp\Metadata;
use function Clockwork_For_Wp\array_only;

final class Api_Controller {
	private $authenticator;

	private $metadata;

	private $request;

	public function __construct(
		AuthenticatorInterface $authenticator,
		Metadata $metadata,
		Incoming_Request $request
	) {
		$this->authenticator = $authenticator;
		$this->metadata = $metadata;
		$this->request = $request;
	}

	public function authenticate(): void {
		$token = $this->authenticator->attempt(
			\array_filter( $this->extract_credentials() ) // @todo Filter necessary?
		);

		\wp_send_json( [ 'token' => $token ], $token ? 200 : 403 );
	}

	public function serve_json( array $params ): void {
		// @todo Handle 404s.
		// @todo Is this really necessary?
		if ( ! \array_key_exists( 'id', $params ) || $params['id'] === null ) {
			return; // @todo
		}

		$id = $params['id'];
		$extended = $params['extended'] ?? null;
		$direction = $params['direction'] ?? null;
		$count = $params['count'] ?? null;

		$authenticated = $this->authenticator->check(
			// @todo Move to route handler invoker?
			$this->request->header( 'X_CLOCKWORK_AUTH' )
		);

		if ( true !== $authenticated ) {
			\wp_send_json(
				[
					'message' => $authenticated,
					'requires' => $this->authenticator->requires(),
				],
				403
			);
		}

		if ( null !== $extended ) {
			$data = $this->metadata->get_extended( $id, $direction, $count );
		} else {
			$data = $this->metadata->get( $id, $direction, $count );
		}

		$data = $this->apply_filters( $data );

		\wp_send_json( $data ); // @todo
	}

	public function update_data( array $params ): void {
		$request = $this->metadata->get( $params['id'] );

		if ( ! $request ) {
			\wp_send_json( [ 'message' => 'Request not found.' ], 404 );
		}

		$content = $this->request->json();
		$token = $content['_token'] ?? '';

		if ( ! $request->updateToken || ! \hash_equals( $request->updateToken, $token ) ) {
			\wp_send_json( [ 'message' => 'Invalid update token.' ], 403 );
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
