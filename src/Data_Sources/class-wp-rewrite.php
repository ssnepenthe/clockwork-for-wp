<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp_Rewrite extends DataSource {
	protected $wp_rewrite;

	public function __construct( $wp_rewrite = null ) {
		$this->set_wp_rewrite( $wp_rewrite );
	}

	public function get_wp_rewrite() {
		return $this->wp_rewrite;
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'Routing' );

		$panel->table( 'Rewrite Settings', $this->settings_table() );
		$panel->table( 'Rewrite Rules', $this->rules_table() );

		return $request;
	}

	public function set_wp_rewrite( $wp_rewrite ) {
		$this->wp_rewrite = is_object( $wp_rewrite ) ? $wp_rewrite : null;
	}

	protected function settings_table() {
		if ( null === $this->wp_rewrite ) {
			return [];
		}

		$structure = $trailing_slash = $front = null;

		if ( property_exists( $this->wp_rewrite, 'permalink_structure' ) ) {
			$structure = $this->wp_rewrite->permalink_structure;
		}

		if ( property_exists( $this->wp_rewrite, 'use_trailing_slashes' ) ) {
			$trailing_slash = $this->wp_rewrite->use_trailing_slashes ? 'Yes' : 'No';
		}

		if ( property_exists( $this->wp_rewrite, 'front' ) ) {
			$trailing_slash = $this->wp_rewrite->front;
		}

		return array_filter( [
			[
				'Item' => 'Permalink Structure',
				'Value' => $structure,
			],
			[
				'Item' => 'Trailing Slash?',
				'Value' => $trailing_slash,
			],
			[
				'Item' => 'Rewrite Front',
				'Value' => $front,
			],
		], function( $row ) {
			return null !== $row['Value'];
		} );
	}

	/**
	 * @return array<int, array>
	 */
	protected function rules_table() {
		if (
			null === $this->wp_rewrite
			|| ! method_exists( $this->wp_rewrite, 'wp_rewrite_rules' )
		) {
			return [];
		}

		// @todo What does wp_rewrite_rules() return when pretty permalinks are disabled?
		$rules = $this->wp_rewrite->wp_rewrite_rules();

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
