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
	public function is_enabled() {
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
