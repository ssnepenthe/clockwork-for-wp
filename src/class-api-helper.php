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

	/**
	 * @hook init
	 */
	public function register_routes() {
		$this->plugin->service( 'routes' )->add( $this->build_extended_route() );
		$this->plugin->service( 'routes' )->add( $this->build_standard_route() );
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
}
