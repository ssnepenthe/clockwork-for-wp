<?php

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork\Storage\StorageInterface;

class Api_Helper {
	const EXTENDED_REWRITE_REGEX = '__clockwork\/([0-9-]+|latest)\/extended';
	const EXTENDED_REWRITE_QUERY = 'index.php?cfw_id=$matches[1]&cfw_extended=1';
	const REWRITE_REGEX = '__clockwork\/([0-9-]+|latest)(?:\/(next|previous))?(?(2)\/(\d+))?';
	const REWRITE_QUERY = 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw_count=$matches[3]';

	const ID_QUERY_VAR = 'cfw_id';
	const DIRECTION_QUERY_VAR = 'cfw_direction';
	const COUNT_QUERY_VAR = 'cfw_count';
	const EXTENDED_QUERY_VAR = 'cfw_extended';

	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function authenticate() {
		if ( '1' !== get_query_var( 'cfw_auth', '0' ) ) {
			return;
		}

		if ( $this->is_json_request() ) {
			// Clockwork as web-app sends POST request as application/json.
			$input = file_get_contents( 'php://input' );
			$decoded = json_decode( $input, true );

			if ( null === $decoded || JSON_ERROR_NONE !== json_last_error() ) {
				$credentials = [];
			} else {
				$credentials = [
					'username' => isset( $decoded['username'] ) ? $decoded['username'] : null,
					'password' => isset( $decoded['password'] ) ? $decoded['password'] : null,
				];
			}
		} else {
			// Clockwork as browser extension sends POST request as multipart/form-data.
			$credentials = [
				'username' => filter_input( INPUT_POST, 'username' ),
				'password' => filter_input( INPUT_POST, 'password' ),
			];
		}

		$token = $this->plugin->service( 'clockwork' )
			->getAuthenticator()
			->attempt( array_filter( $credentials ) );

		if ( ! $token ) {
			status_header( 403 );
		}

		wp_send_json( [ 'token' => $token ] );
	}

	/**
	 * @hook init
	 */
	public function register_routes() {
		$this->plugin->service( 'routes' )->add( $this->build_extended_route() );
		$this->plugin->service( 'routes' )->add( $this->build_standard_route() );
		$this->plugin->service( 'routes' )->add( $this->build_authentication_route() );
	}

	public function send_headers() {
		// @todo Include default for the case where request uri is not set?
		if ( $this->plugin->is_uri_filtered( $_SERVER['REQUEST_URI'] ) || headers_sent() ) {
			return;
		}

		// @todo Any reason to suppress errors?
		// @todo Request as a direct dependency?
		header( 'X-Clockwork-Id: ' . $this->plugin->service( 'clockwork' )->getRequest()->id );
		header( 'X-Clockwork-Version: ' . Clockwork::VERSION );

		// @todo Set clockwork path header?

		$extra_headers = $this->plugin->service( 'config' )->get_headers();

		foreach ( $extra_headers as $header_name => $header_value ) {
			header( "X-Clockwork-Header-{$header_name}: {$header_value}" );
		}

		// @todo Set subrequest headers?
	}

	/**
	 * @return void
	 */
	public function serve_json() {
		// @todo Handle 404s.
		$id = get_query_var( self::ID_QUERY_VAR, null );

		if ( null === $id ) {
			return; // @todo
		}

		$token = isset( $_SERVER['HTTP_X_CLOCKWORK_AUTH'] )
			? $_SERVER['HTTP_X_CLOCKWORK_AUTH'] :
			'';
		$authenticator = $this->plugin->service( 'clockwork' )->getAuthenticator();
		$authenticated = $authenticator->check( $token );

		if ( $authenticated !== true ) {
			status_header( 403 );

			wp_send_json( [
				'message' => $authenticated,
				'requires' => $authenticator->requires(),
			] );
		}

		$direction = get_query_var( self::DIRECTION_QUERY_VAR, null );
		$count = get_query_var( self::COUNT_QUERY_VAR, null );
		$extended = get_query_var( self::EXTENDED_QUERY_VAR, null );

		if ( 'previous' !== $direction && 'next' !== $direction ) {
			$direction = null;
		}

		if ( null !== $count ) {
			$count = (int) $count;
		}

		if ( null !== $extended ) {
			$extended = true;
		}

		$data = $this->get_data( $id, $direction, $count, $extended );

		wp_send_json( $data ); // @todo
	}

	protected function build_authentication_route() {
		$route = new Route( '__clockwork\/auth', 'index.php?cfw_auth=1' );

		$route->set_query_vars( [ 'cfw_auth' ] );

		$route->map( 'POST', [ $this, 'authenticate' ] );

		return $route;
	}

	protected function build_extended_route() {
		$route = new Route( self::EXTENDED_REWRITE_REGEX, self::EXTENDED_REWRITE_QUERY );

		$route->set_query_vars( [ self::ID_QUERY_VAR, self::EXTENDED_QUERY_VAR ] );

		$route->map( 'GET', [ $this, 'serve_json' ] );

		return $route;
	}

	protected function build_standard_route() {
		$route = new Route( self::REWRITE_REGEX, self::REWRITE_QUERY );

		$route->set_query_vars( [
			self::ID_QUERY_VAR,
			self::DIRECTION_QUERY_VAR,
			self::COUNT_QUERY_VAR,
		] );

		$route->map( 'GET', [ $this, 'serve_json' ] );

		return $route;
	}

	protected function get_data( $id = null, $direction = null, $count = null, $extended = null ) {
		if ( 'previous' === $direction ) {
			$data = $this->plugin->service( 'clockwork.storage' )->previous( $id, $count );
		} elseif ( 'next' === $direction ) {
			$data = $this->plugin->service( 'clockwork.storage' )->next( $id, $count );
		} elseif ( 'latest' === $id ) {
			$data = $this->plugin->service( 'clockwork.storage' )->latest();
		} else {
			$data = $this->plugin->service( 'clockwork.storage' )->find( $id );
		}

		if ( $extended ) {
			$this->plugin->service( 'clockwork' )->extendRequest( $data );
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
}
