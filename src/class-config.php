<?php

namespace Clockwork_For_Wp;

class Config {
	protected $authentication;
	protected $authentication_password;
	protected $collect_data_always;
	protected $disabled_data_sources;
	protected $enabled;
	protected $filtered_uris;
	protected $headers;
	protected $storage_expiration;
	protected $storage_files_path;
	protected $storage_filter;
	protected $web_enabled;

	public function __construct( array $args = [] ) {
		$defaults = [
			// Require authentication to access the Clockwork API.
			'authentication' => false,

			// Shared password to use when authentication is enabled.
			'authentication_password' => 'CHANGEMEPLEASE',

			// Enable metadata collection even when everything else is disabled.
			'collect_data_always' => false,

			// Disable individual data sources.
			'disabled_data_sources' => [ 'wp_rewrite', 'rest_api' ],

			// Enable Clockwork - Ensures headers are sent, API is available, web app is available.
			'enabled' => true,

			// URI patterns for which metadata collection should be disabled.
			// @todo Ensure "__clockwork" URIs are always filtered no matter what the user does here?
			'filtered_uris' => [ '\/__clockwork(?:\/.*)?' ],

			// Additional headers to be sent with responses for which metadata has been collected.
			'headers' => [],

			// Amount of time metadata should be saved.
			'storage_expiration' => 60 * 24 * 7,

			// Location in which metadata JSON files should be saved.
			'storage_files_path' => WP_CONTENT_DIR . '/cfw-data',

			// Properties to be stripped from request metadata before saving.
			'storage_filter' => [],

			// Enable/disable the Clockwork web app.
			'web_enabled' => true,
		];

		foreach ( array_merge( $defaults, $args ) as $key => $value ) {
			$method = "set_{$key}";

			if ( method_exists( $this, $method ) ) {
				$this->{$method}( $value );
			}
		}
	}

	/**
	 * @return string
	 */
	public function get_authentication_password() {
		return $this->authentication_password;
	}

	/**
	 * @return array<integer, string>
	 */
	public function get_disabled_data_sources() {
		return $this->disabled_data_sources;
	}

	/**
	 * @return array<integer, string>
	 */
	public function get_filtered_uris() {
		return $this->filtered_uris;
	}

	/**
	 * @return array<string, string>
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * @return integer
	 */
	public function get_storage_expiration() {
		return $this->storage_expiration;
	}

	/**
	 * @return string
	 */
	public function get_storage_files_path() {
		return $this->storage_files_path;
	}

	/**
	 * @return array<integer, string>
	 */
	public function get_storage_filter() {
		return $this->storage_filter;
	}

	/**
	 * @return boolean
	 */
	public function is_authentication_required() {
		return $this->authentication;
	}

	/**
	 * @return boolean
	 */
	public function is_collecting_data_always() {
		return $this->collect_data_always;
	}

	/**
	 * @return boolean
	 */
	public function is_enabled() {
		return $this->enabled;
	}

	/**
	 * @return boolean
	 */
	public function is_web_enabled() {
		return $this->web_enabled;
	}

	public function set_authentication( $authentication ) {
		$this->authentication = (bool) $authentication;

		return $this;
	}

	public function set_authentication_password( $authentication_password ) {
		$this->authentication_password = (string) $authentication_password;

		return $this;
	}

	public function set_collect_data_always( $collect_data_always ) {
		$this->collect_data_always = (bool) $collect_data_always;

		return $this;
	}

	public function set_disabled_data_sources( array $identifiers ) {
		$this->disabled_data_sources = array_values( array_map( 'strval', $identifiers ) );

		return $this;
	}

	public function set_enabled( $enabled ) {
		$this->enabled = (bool) $enabled;

		return $this;
	}

	public function set_filtered_uris( array $filtered_uris ) {
		$this->filtered_uris = array_values( array_map( 'strval', $filtered_uris ) );

		return $this;
	}

	public function set_headers( array $headers ) {
		$new_headers = [];

		foreach ( $headers as $name => $value ) {
			$new_headers[ (string) $name ] = (string) $value;
		}

		$this->headers = $new_headers;

		return $this;
	}

	public function set_storage_expiration( $storage_expiration ) {
		$this->storage_expiration = (int) $storage_expiration;

		return $this;
	}

	public function set_storage_files_path( $storage_files_path ) {
		$this->storage_files_path = (string) $storage_files_path;

		return $this;
	}

	public function set_storage_filter( array $storage_filter ) {
		$this->storage_filter = array_values( array_map( 'strval', $storage_filter ) );

		return $this;
	}

	public function set_web_enabled( $web_enabled ) {
		$this->web_enabled = (bool) $web_enabled;

		return $this;
	}
}
