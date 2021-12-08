<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use InvalidArgumentException;

use function Clockwork_For_Wp\describe_value;

final class Constants extends DataSource {
	/**
	 * @var array<int, array{constant: string, when?: callable}>
	 */
	private $constants = [];

	public function __construct( array $constants = [] ) {
		$this->set_constants( $constants );
	}

	public function add_constant( string $constant, ?callable $when = null ): self {
		$constant = [ 'constant' => $constant ];

		if ( $when ) {
			$constant['when'] = $when;
		}

		$this->constants[] = $constant;

		return $this;
	}

	public function resolve( Request $request ): Request {
		$table = $this->build_table();

		if ( [] !== $table ) {
			$request->userData( 'WordPress' )->table( 'Constants', $table );
		}

		return $request;
	}

	/**
	 * @param array<int, array{constant: string, when?: callable}> $constants
	 */
	public function set_constants( array $constants ): self {
		foreach ( $constants as $constant ) {
			$this->add_constant( $constant['constant'], $constant['when'] ?? null );
		}

		return $this;
	}

	/**
	 * @return array<int, array{constant: string, value: string}>
	 */
	private function build_table(): array {
		$table = [];

		foreach ( $this->constants as $constant ) {
			if ( \array_key_exists( 'when', $constant ) && ! $constant['when']() ) {
				continue;
			}

			$value = \defined( $constant['constant'] )
				? describe_value( \constant( $constant['constant'] ) )
				: '(NOT DEFINED)';

			$table[] = [
				'constant' => $constant['constant'],
				'value' => $value,
			];
		}

		\usort(
			$table,
			/**
			 * @param array{constant: string, value: string} $a
			 * @param array{constant: string, value: string} $b
			 */
			static function ( array $a, array $b ): int {
				return $a['constant'] <=> $b['constant'];
			}
		);

		return $table;
	}

	public static function from( array $config ): self {
		$constants = $config['constants'] ?? [];
		$data_source = new self();

		if ( ! \is_array( $constants ) ) {
			throw new InvalidArgumentException( 'Constants list from config must be an array' );
		}

		foreach ( $constants as $constant ) {
			if ( \is_string( $constant ) ) {
				$constant = [ 'constant' => $constant ];
			}

			if ( ! \is_array( $constant ) ) {
				throw new InvalidArgumentException(
					'Constants must be of type "string" or "array"'
				);
			}

			if (
				! \array_key_exists( 'constant', $constant )
				|| ! \is_string( $constant['constant'] )
			) {
				throw new InvalidArgumentException(
					'Constant array must have key "constant" with value of type "string"'
				);
			}

			if (
				\array_key_exists( 'when', $constant )
				&& ! \is_callable( $constant['when'] )
			) {
				throw new InvalidArgumentException(
					'Optional constant "when" condition must be of type "callable"'
				);
			}

			$data_source->add_constant( $constant['constant'], $constant['when'] ?? null );
		}

		return $data_source;
	}
}
