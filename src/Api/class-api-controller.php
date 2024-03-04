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

		// @todo Verify this is still necessary with latest versions of clockwork.
		// At one point the clockwork browser extension was sending multipart/form-data requests but the web app was sending application/json.
		$credentials = $this->request->is_json()
			? array_only( $this->request->json(), [ 'username', 'password' ] )
			: array_filter( [
				'username' => \filter_input( \INPUT_POST, 'username' ),
				'password' => \filter_input( \INPUT_POST, 'password' ),
			] );

		$token = $this->authenticator->attempt( $credentials );

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
				? $request->only( \array_diff( $only, [ 'updateToken' ] ) )
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
}
