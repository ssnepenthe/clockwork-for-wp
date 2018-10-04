<?php

namespace Clockwork_For_Wp\Data_Sources;

use Clockwork\DataSource\PhpDataSource;

class Php extends PhpDataSource {
	public function removePasswords( array $data ) {
		$keys = array_keys( $data );
		$values = array_map( function( $value, $key ) {
			$cookies = [ AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE ];

			if (
				// @todo Filterable pattern(s) for data to remove?
				preg_match( '/pass|pwd/i', $key ) === 1
				|| preg_match( '/' . implode( '|', $cookies ) . '/i', $key ) === 1
			) {
				return '*removed*';
			}

			return $value;
		}, $data, $keys );

		return array_combine( $keys, $values );
	}

	protected function getRequestHeaders() {
		$headers = parent::getRequestHeaders();

		// We are removing sensitive data from the $_COOKIE array elsewhere...
		// Lets make sure to parse the cookie headers so we can remove the same data.
		$headers['Cookie'] = array_map( function( $cookie_string ) {
			$clean = array_map( function( $pair ) {
				list( $key, $value ) = explode( '=', trim( $pair ) );

				$clean = $this->removePasswords( [ $key => $value ] );

				$key = key( $clean );
				$value = current( $clean );

				return "{$key}={$value}";
			}, explode( ';', $cookie_string ) );

			return implode( '; ', $clean );
		}, isset( $headers['Cookie'] ) ? $headers['Cookie'] : [] );

		return $headers;
	}
}
