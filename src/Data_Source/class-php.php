<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\PhpDataSource;

final class Php extends PhpDataSource {
	private $sensitive_patterns;

	public function __construct( string ...$sensitive_patterns ) {
		$this->sensitive_patterns = $sensitive_patterns;
	}

	public function removePasswords( array $data ) {
		// @todo Should we also allow for removing data from keys?
		// @todo Should this override apply to all data sources?
		$values = \array_map(
			function ( $value, $key ) {
				foreach ( $this->sensitive_patterns as $pattern ) {
					if ( 1 === \preg_match( $pattern, $key ) ) {
						return '*removed*';
					}
				}

				return $value;
			},
			$data,
			$keys = \array_keys( $data )
		);

		return \array_combine( $keys, $values );
	}

	protected function getRequestHeaders() {
		$headers = parent::getRequestHeaders();

		if ( \array_key_exists( 'Cookie', $headers ) ) {
			// We are removing sensitive data from the $_COOKIE array elsewhere...
			// Lets make sure to parse the cookie headers so we can remove the same data.
			$headers['Cookie'] = \array_map(
				function ( $cookie_string ) {
					$clean = \array_map(
						function ( $pair ) {
							[ $key, $value ] = \explode( '=', \trim( $pair ) );

							$clean = $this->removePasswords( [ $key => $value ] );

							$key = \key( $clean );
							$value = \current( $clean );

							return "{$key}={$value}";
						},
						\explode( ';', $cookie_string )
					);

					return \implode( '; ', $clean );
				},
				$headers['Cookie']
			);
		}

		return $headers;
	}
}
