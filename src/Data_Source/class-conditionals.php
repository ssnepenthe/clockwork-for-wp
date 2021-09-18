<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use function Clockwork_For_Wp\describe_callable;
use function Clockwork_For_Wp\describe_value;

final class Conditionals extends DataSource {
	/**
	 * @var array<int, array{conditional: callable, label?: string, when?: callable}>
	 */
	private $conditionals = [];

	/**
	 * @param array<int, array{conditional: callable, label?: string, when?: callable}> $conditionals
	 */
	public function __construct( array $conditionals = [] ) {
		$this->set_conditionals( $conditionals );
	}

	public function add_conditional(
		callable $conditional,
		?string $label = null,
		?callable $when = null
	): self {
		$conditional = [ 'conditional' => $conditional ];

		if ( $label ) {
			$conditional['label'] = $label;
		}

		if ( $when ) {
			$conditional['when'] = $when;
		}

		$this->conditionals[] = $conditional;

		return $this;
	}

	public function resolve( Request $request ): Request {
		if ( [] !== $this->conditionals ) {
			$request->userData( 'WordPress' )->table( 'Conditionals', $this->build_table() );
		}

		return $request;
	}

	/**
	 * @param array<int, array{conditional: callable, label?: string, when?: callable}> $conditionals
	 */
	public function set_conditionals( array $conditionals ): self {
		foreach ( $conditionals as $conditional ) {
			$this->add_conditional(
				$conditional['conditional'],
				$conditional['label'] ?? null,
				$conditional['when'] ?? null
			);
		}

		return $this;
	}

	/**
	 * @return list<array{conditional: string, value: string}>
	 */
	private function build_table(): array {
		$table = [];

		foreach ( $this->conditionals as $conditional ) {
			if ( \array_key_exists( 'when', $conditional ) && ! $conditional['when']() ) {
				continue;
			}

			$callable = $conditional['conditional'];
			$label = $conditional['label'] ?? describe_callable( $callable );
			$value = $callable();
			$description = describe_value( $value );

			if ( ! \is_bool( $value ) ) {
				if ( $value ) {
					$description = "TRUTHY ({$description})";
				} else {
					$description = "FALSEY ({$description})";
				}
			}

			$table[] = [
				'conditional' => $label,
				'value' => $description,
			];
		}

		\usort(
			$table,
			/**
			 * @param array{conditional: string, value: string} $a
			 * @param array{conditional: string, value: string} $b
			 */
			static function ( array $a, array $b ): int {
				if ( $a['value'] === $b['value'] ) {
					return $a['conditional'] <=> $b['conditional'];
				}

				// Reverse - we want TRUE to come before FALSE.
				return $b['value'] <=> $a['value'];
			}
		);

		return $table;
	}
}
