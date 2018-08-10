<?php

namespace Clockwork_For_Wp\Data_Source;

use WP_Rewrite as Rewrite;
use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp_Rewrite extends DataSource {
	protected $rewrite;

	public function __construct( Rewrite $rewrite ) {
		$this->rewrite = $rewrite;
	}

	public function resolve( Request $request ) {
		$request->routes = $this->collect_routes();

		return $request;
	}

	/**
	 * @return array<int, array>
	 */
	protected function collect_routes() {
		// @todo What does wp_rewrite_rules() return when pretty permalinks are disabled?
		$rules = $this->rewrite->wp_rewrite_rules();

		// @todo Routes may not be the most appropriate place to put all of the WordPress rewrites...
		return array_map(
			/**
			 * @param        string $regex
			 * @param        string $query
			 * @psalm-return array{uri: string, action: string}
			 */
			function( $regex, $query ) {
				return [
					'uri' => $query,
					'action' => $regex,
				];
			},
			$rules,
			array_keys( $rules )
		);
	}
}
