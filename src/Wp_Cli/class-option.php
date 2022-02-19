<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use RuntimeException;

/**
 * @internal
 */
final class Option {
	private $default;

	private $description;

	private $name;

	private $optional = true;

	private $options = [];

	private $value_is_optional = false;

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

	public function get_synopsis(): array {
		$synopsis = [
			'name' => $this->name,
			'optional' => $this->optional,
			'repeating' => false,
			'type' => 'assoc',
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

		// @todo This doesn't currently work.
		// Revisit if/when https://github.com/wp-cli/wp-cli/pull/5618 is merged.
		if ( $this->value_is_optional ) {
			$synopsis['value'] = [
				'optional' => true,
				'name' => $this->name,
			];
		}

		return $synopsis;
	}

	public function get_value_is_optional(): bool {
		return $this->value_is_optional;
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
		if ( $this->value_is_optional ) {
			throw new RuntimeException(
				'Cannot modify optional flag when value_is_optional flag has been set to true'
			);
		}

		$this->optional = $optional;

		return $this;
	}

	public function set_options( string ...$options ) {
		$this->options = $options;

		return $this;
	}

	public function set_value_is_optional( bool $value_is_optional ) {
		if ( ! $this->optional ) {
			throw new RuntimeException(
				'Cannot modify value_is_optional flag when optional flag has been set to false'
			);
		}

		$this->value_is_optional = $value_is_optional;

		return $this;
	}
}
