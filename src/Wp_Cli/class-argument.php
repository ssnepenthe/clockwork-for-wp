<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

/**
 * @internal
 */
final class Argument {
	private $default;

	private $description;

	private $name;

	private $optional = false;

	private $options = [];

	private $repeating = false;

	public function __construct( string $name ) {
		$this->name = $name;
	}

	public function get_default(): ?string {
		return $this->default;
	}

	public function get_description(): ?string {
		return $this->description;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_optional(): bool {
		return $this->optional;
	}

	public function get_options(): array {
		return $this->options;
	}

	public function get_repeating(): bool {
		return $this->repeating;
	}

	public function get_synopsis(): array {
		$synopsis = [
			'type' => 'positional',
			'name' => $this->name,
			'optional' => $this->optional,
			'repeating' => $this->repeating,
		];

		if ( $this->description ) {
			$synopsis['description'] = $this->description;
		}

		if ( \is_string( $this->default ) ) {
			$synopsis['default'] = $this->default;
		}

		if ( ! empty( $this->options ) ) {
			$synopsis['options'] = $this->options;
		}

		return $synopsis;
	}

	public function set_default( string $default ) {
		$this->default = $default;

		return $this;
	}

	public function set_description( string $description ) {
		$this->description = $description;

		return $this;
	}

	public function set_optional( bool $optional ) {
		$this->optional = $optional;

		return $this;
	}

	public function set_options( string ...$options ) {
		$this->options = $options;

		return $this;
	}

	public function set_repeating( bool $repeating ) {
		$this->repeating = $repeating;

		return $this;
	}
}
