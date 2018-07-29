<?php

namespace Clockwork_For_Wp;

class Config {
	protected $enabled;
	protected $filter;
	protected $filtered_uris;
	protected $headers;
	protected $server_timing;
	protected $storage_expiration;
	protected $storage_files_path;
	protected $web_enabled;

	public function __construct( array $args = [] ) {
		$defaults = [
			'enabled' => true,
			'filter' => [ 'routes' ],
			'filtered_uris' => [ '\/__clockwork(?:\/.*)?' ],
			'headers' => [],
			'server_timing' => 10,
			'storage_expiration' => 60 * 24 * 7,
			'storage_files_path' => WP_CONTENT_DIR . '/cfw-data',
			'web_enabled' => true,
		];

		foreach ( wp_parse_args( $args, $defaults ) as $key => $value ) {
			$method = "set_{$key}";

			if ( method_exists( $this, $method ) ) {
				$this->{$method}( $value );
			}
		}
	}

	/**
	 * @return boolean
	 */
	public function is_collecting_data() {
		return $this->is_enabled();
	}

	/**
	 * @return boolean
	 */
	public function is_collecting_cache_data() {
		return ! in_array( 'cache', $this->get_filter(), true );
	}

	/**
	 * @return boolean
	 */
	public function is_collecting_db_data() {
		return defined( 'SAVEQUERIES' )
			&& SAVEQUERIES
			&& ! in_array( 'databaseQueries', $this->get_filter(), true );
	}

	/**
	 * @return boolean
	 */
	public function is_collecting_email_data() {
		return ! in_array( 'emailsData', $this->get_filter(), true );
	}

	/**
	 * @return boolean
	 */
	public function is_collecting_event_data() {
		return ! in_array( 'events', $this->get_filter(), true );
	}

	/**
	 * @return boolean
	 */
	public function is_collecting_rewrite_data() {
		return ! in_array( 'routes', $this->get_filter(), true );
	}

	/**
	 * @return boolean
	 */
	public function is_collecting_theme_data() {
		return ! in_array( 'viewsData', $this->get_filter(), true );
	}

	/**
	 * @return boolean
	 */
	public function is_enabled() {
		return $this->enabled;
	}

	public function set_enabled( $enabled ) {
		$this->enabled = (bool) $enabled;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function is_web_enabled() {
		return $this->web_enabled;
	}

	public function set_web_enabled( $web_enabled ) {
		$this->web_enabled = (bool) $web_enabled;

		return $this;
	}

	/**
	 * @return array<int, string>
	 */
	public function get_filter() {
		return $this->filter;
	}

	public function set_filter( array $filter ) {
		$this->filter = array_values( array_map( function( $f ) {
			return (string) $f;
		}, $filter ) );

		return $this;
	}

	/**
	 * @return array<int, string>
	 */
	public function get_filtered_uris() {
		return $this->filtered_uris;
	}

	public function set_filtered_uris( array $filtered_uris ) {
		$this->filtered_uris = array_values( array_map( function( $uri ) {
			return (string) $uri;
		}, $filtered_uris ) );
	}

	/**
	 * @return array<string, string>
	 */
	public function get_headers() {
		return $this->headers;
	}

	public function set_headers( array $headers ) {
		$new_headers = [];

		foreach ( $headers as $name => $value ) {
			$new_headers[ (string) $name ] = (string) $value;
		}

		$this->headers = $new_headers;

		return $this;
	}

	/**
	 * @return false|integer
	 *
	 * @todo Better name?
	 */
	public function get_server_timing() {
		return $this->server_timing;
	}

	public function set_server_timing( $server_timing ) {
		$this->server_timing = (int) $server_timing;

		return $this;
	}

	/**
	 * @return integer
	 */
	public function get_storage_expiration() {
		return $this->storage_expiration;
	}

	public function set_storage_expiration( $storage_expiration ) {
		$this->storage_expiration = (int) $storage_expiration;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_storage_files_path() {
		return $this->storage_files_path;
	}

	public function set_storage_files_path( $storage_files_path ) {
		$this->storage_files_path = (string) $storage_files_path;

		return $this;
	}
}
