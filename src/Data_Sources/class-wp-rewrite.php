<?php

namespace Clockwork_For_Wp\Data_Sources;

use WP_Rewrite as Rewrite;
use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp_Rewrite extends DataSource {
	protected $rewrite;

	public function __construct( Rewrite $rewrite ) {
		$this->rewrite = $rewrite;
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'rewrites' )->title( 'Rewrites' );

		$panel->table( 'Miscellaneous', $this->miscellaneous_table() );
		$panel->table( 'Rules', $this->rules_table() );

		return $request;
	}

	protected function miscellaneous_table() {
		return [
			[
				'Item' => 'Permalink Structure',
				'Value' => $this->rewrite->permalink_structure,
			],
			[
				'Item' => 'Trailing Slash?',
				'Value' => $this->rewrite->use_trailing_slashes ? 'Yes' : 'No',
			],
			[
				'Item' => 'Rewrite Front',
				'Value' => $this->rewrite->front,
			],
		];
	}

	/**
	 * @return array<int, array>
	 */
	protected function rules_table() {
		// @todo What does wp_rewrite_rules() return when pretty permalinks are disabled?
		$rules = $this->rewrite->wp_rewrite_rules();

		return array_map(
			/**
			 * @param        string $regex
			 * @param        string $query
			 * @psalm-return array{uri: string, action: string}
			 */
			function( $regex, $query ) {
				return [
					'Query' => $query,
					'Regex' => $regex,
				];
			},
			$rules,
			array_keys( $rules )
		);
	}
}
