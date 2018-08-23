<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\PhpDataSource;

class Php extends PhpDataSource {
	public function removePasswords( array $data ) {
		$keys = array_keys( $data );
		$values = array_map( function( $value, $key ) {
			return preg_match( '/pass|pwd/i', $key ) === 1 ? '*removed*' : $value;
		}, $data, $keys );

		return array_combine( $keys, $values );
	}
}
