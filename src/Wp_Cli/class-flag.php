<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

/**
 * @internal
 */
final class Flag {
	private $description;

	private $name;

	public function __construct( string $name ) {
		$this->name = $name;
	}

	public function get_description(): ?string {
		return $this->description;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_synopsis(): array {
		$synopsis = [
			'type' => 'flag',
			'name' => $this->name,
			'optional' => true,
			'repeating' => false,
		];

		if ( $this->description ) {
			$synopsis['description'] = $this->description;
		}

		return $synopsis;
	}

	public function set_description( string $description ) {
		$this->description = $description;

		return $this;
	}
}
