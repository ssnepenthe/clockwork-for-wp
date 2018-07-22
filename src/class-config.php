<?php

namespace Clockwork_For_Wp;

class Config {
	public function is_collecting_data() {
		return $this->is_enabled();
	}

	public function is_enabled() {
		return true;
	}

	public function get_filter() {
		return [];
	}

	public function get_filtered_uris() {
		return [ '/__clockwork(?:/.*)?' ];
	}

	public function get_headers() {
		return [];
	}

	// @todo Better name?
	public function get_server_timing() {
		return 10;
	}

	public function get_storage_expiration() {
		return 60 * 24 * 7;
	}

	public function get_storage_files_path() {
		return WP_CONTENT_DIR . '/cfw-data';
	}
}
