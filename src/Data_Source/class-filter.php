<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Closure;
use InvalidArgumentException;

/**
 * @internal
 */
final class Filter {
	private $except;

	private $only;

	public function __invoke( $value ) {
		return $this->passes( $value );
	}

	public function except( array $patterns ) {
		$this->except = \implode( '|', $patterns );

		return $this;
	}

	public function only( array $patterns ) {
		$this->only = \implode( '|', $patterns );

		return $this;
	}

	public function passes( string $value ): bool {
		if ( $this->only ) {
			return 1 === \preg_match( "/{$this->only}/", $value );
		}

		if ( $this->except ) {
			return 1 !== \preg_match( "/{$this->except}/", $value );
		}

		return true;
	}

	public function to_closure( ?string $key = null ): Closure {
		return function ( $value ) use ( $key ) {
			if ( \is_string( $key ) ) {
				if ( ! \is_array( $value ) ) {
					throw new InvalidArgumentException(
						'Cannot check non-array values when key is provided'
					);
				}

				if ( ! \array_key_exists( $key, $value ) ) {
					throw new InvalidArgumentException( "Value to check must contain key {$key}" );
				}

				$value = $value[ $key ];
			}

			return $this->passes( $value );
		};
	}
}
