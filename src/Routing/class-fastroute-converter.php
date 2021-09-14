<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use FastRoute\BadRouteException;
use FastRoute\RouteParser\Std;
use InvalidArgumentException;

final class Fastroute_Converter {
	/**
	 * @var callable
	 */
	private $additional_param_resolver;

	/**
	 * @var Std
	 */
	private $parser;

	private $prefix;

	public function __construct( string $prefix = '', ?callable $param_resolver = null ) {
		$this->prefix = $prefix;
		$this->parser = new Std();

		$this->resolve_additional_params_using(
			$param_resolver ?: static function (): array {
				return [];
			}
		);
	}

	public function convert( string $route ): array {
		// @todo Duplicate route detection?
		if ( '' === $route ) {
			throw new BadRouteException( 'Empty routes not allowed' );
		}

		// @todo Catch and rethrow fast-route exceptions as plugin-specific exceptions?
		$parsed = $this->parser->parse( $route );
		$rewrites = [];

		foreach ( $parsed as $segments ) {
			if ( '' === $segments[0] ) {
				throw new BadRouteException( 'Empty routes not allowed' );
			}

			$regex = '';
			$query_array = [];
			$position = 1;

			foreach ( $segments as $segment ) {
				if ( \is_string( $segment ) ) {
					$regex .= $segment;

					continue;
				}

				[ $name, $pattern ] = $segment;

				$regex .= "({$pattern})";
				$query_array[ "{$this->prefix}{$name}" ] = "\$matches[{$position}]";
				$position++;
			}

			$query_array = $this->resolve_additional_params( $regex, $query_array );

			$rewrites[ "^{$regex}$" ] = $query_array;
		}

		return $rewrites;
	}

	public function resolve_additional_params_using( callable $callback ): void {
		$this->additional_param_resolver = $callback;
	}

	private function resolve_additional_params( string $regex, array $query_array ): array {
		$additional_params = ( $this->additional_param_resolver )(
			$regex,
			$query_array,
			$this->prefix
		);

		if ( ! \is_array( $additional_params ) ) {
			throw new InvalidArgumentException( 'Additional param resolver must return an array' );
		}

		foreach ( $additional_params as $key => $value ) {
			if ( ! \is_string( $key ) || ! \is_string( $value ) ) {
				throw new InvalidArgumentException(
					'Additional param resolver must return an array with string keys and string values'
				);
			}

			if ( \array_key_exists( $key, $query_array ) ) {
				throw new InvalidArgumentException(
					'Additional param resolver must not overwrite parsed query params'
				);
			}

			$query_array[ $key ] = $value;
		}

		$query_array[ "{$this->prefix}matched_route" ] = \md5( $regex );

		return $query_array;
	}
}
