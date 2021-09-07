<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Subscriber;
use function Clockwork_For_Wp\describe_value;

final class Wp_Rewrite extends DataSource implements Subscriber {
	private $front = '';
	private $rules = [];
	private $structure = '';
	private $trailing_slash = false;

	public function add_rule( $regex, $query ) {
		$this->rules[] = [
			'Regex' => $regex,
			'Query' => $query,
		];

		return $this;
	}

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => function ( \WP_Rewrite $wp_rewrite ): void {
				$this
					->set_structure( $wp_rewrite->permalink_structure )
					->set_trailing_slash( $wp_rewrite->use_trailing_slashes )
					->set_front( $wp_rewrite->front )
					->set_rules( $wp_rewrite->wp_rewrite_rules() );
			},
		];
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'Routing' );

		$panel->table(
			'Rewrite Settings',
			[
				[
					'Item' => 'Permalink Structure',
					'Value' => $this->structure,
				],
				[
					'Item' => 'Trailing Slash?',
					'Value' => describe_value( $this->trailing_slash ),
				],
				[
					'Item' => 'Rewrite Front',
					'Value' => $this->front,
				],
			]
		);
		$panel->table( 'Rewrite Rules', $this->rules );

		return $request;
	}

	public function set_front( string $front ) {
		$this->front = $front;

		return $this;
	}

	public function set_rules( array $rules ) {
		$this->rules = [];

		// @todo is this backwards?
		foreach ( $rules as $regex => $query ) {
			$this->add_rule( $regex, $query );
		}

		return $this;
	}

	public function set_structure( string $structure ) {
		$this->structure = $structure;

		return $this;
	}

	public function set_trailing_slash( bool $trailing_slash ) {
		$this->trailing_slash = $trailing_slash;

		return $this;
	}
}
