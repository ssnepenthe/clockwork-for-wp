<?php

namespace Clockwork_For_Wp;

class Config {
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
		return true;
	}

	/**
	 * @return boolean
	 */
	public function is_web_enabled() {
		return true;
	}

	/**
	 * @return array<int, string>
	 */
	public function get_filter() {
		return [];
	}

	/**
	 * @return array<int, string>
	 */
	public function get_filtered_uris() {
		return [ '/__clockwork(?:/.*)?' ];
	}

	/**
	 * @return array<string, string>
	 */
	public function get_headers() {
		return [];
	}

	/**
	 * @return false|integer
	 *
	 * @todo Better name?
	 */
	public function get_server_timing() {
		return 10;
	}

	/**
	 * @return integer
	 */
	public function get_storage_expiration() {
		return 60 * 24 * 7;
	}

	/**
	 * @return string
	 */
	public function get_storage_files_path() {
		return WP_CONTENT_DIR . '/cfw-data';
	}
}
