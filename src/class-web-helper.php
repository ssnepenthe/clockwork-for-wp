<?php

namespace Clockwork_For_Wp;

use Clockwork\Web\Web;

class Web_Helper {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	// @todo Move to request helper and ensure this covers both api and web requests?
	public function prevent_canonical_redirect( $redirect, $requested ) {
		$clockwork = home_url( '__clockwork' );

		if ( $clockwork === substr( $requested, 0, strlen( $clockwork ) ) ) {
			return $requested;
		}

		return $redirect;
	}

	public function redirect_shortcut() {
		$clockwork = home_url( '__clockwork', 'relative' );

		if (
			! isset( $_SERVER['REQUEST_URI'] )
			|| $clockwork !== untrailingslashit( $_SERVER['REQUEST_URI'] )
		) {
			return;
		}

		wp_safe_redirect( home_url( '__clockwork/app' ) );
		die;
	}

	/**
	 * @hook init
	 */
	public function register_routes() {
		$this->plugin->service( 'routes' )->add( $this->build_app_route() );
		$this->plugin->service( 'routes' )->add( $this->build_assets_route() );
	}

	public function serve_web_assets() {
		if ( '1' !== get_query_var( 'cfw_app', '0' ) ) {
			return;
		}

		$asset = untrailingslashit( get_query_var( 'cfw_asset', 'app.html' ) );

		if ( 'app.html' !== $asset ) {
			$asset = "assets/{$asset}";
		}

		$file = ( new Web() )->asset( $asset );

		if ( is_array( $file ) && isset( $file['path'] ) && is_file( $file['path'] ) ) {
			$size = filesize( $file['path'] );

			// @todo Are any other headers necessary?
			header( "Content-Type: {$file['mime']}" );
			header( "Content-Length: {$size}" );

			readfile( $file['path'] );
			die;
		}

		// @todo Should we handle 404 earlier than template_redirect? Can we make the wp class handle it for us?
		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	protected function build_app_route() {
		$route = new Route( '__clockwork/app', 'index.php?cfw_app=1&cfw_asset=app.html' );

		$route->set_query_vars( [ 'cfw_app', 'cfw_asset' ] );

		$route->map( 'GET', [ $this, 'serve_web_assets' ] );

		return $route;
	}

	protected function build_assets_route() {
		$route = new Route( '__clockwork/assets/(.*)', 'index.php?cfw_app=1&cfw_asset=$matches[1]' );

		$route->set_query_vars( [ 'cfw_app', 'cfw_asset' ] );

		$route->map( 'GET', [ $this, 'serve_web_assets' ] );

		return $route;
	}
}
