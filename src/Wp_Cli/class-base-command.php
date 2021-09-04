<?php

namespace Clockwork_For_Wp\Wp_Cli;

use RuntimeException;
use WP_CLI\SynopsisParser;

abstract class Base_Command {
	protected $arguments = [];
	protected $description;
	protected $name;
	protected $options = [];

	public function arguments(): array {
		return array_map( function ( $synopsis, $description ) {
			$parsed = SynopsisParser::parse( $synopsis )[0];
			$parsed['description'] = $description;

			return $parsed;
		}, array_keys( $this->arguments ), $this->arguments );
	}

	public function longdesc(): string {
		$all_params = array_merge( $this->arguments, $this->options );
		$param_count = count( $all_params );

		if ( $param_count < 1 ) {
			return '';
		}

		$longdesc = [ '## OPTIONS' ];

		foreach ( $all_params as $synopsis => $description ) {
			$longdesc = array_merge( $longdesc, [ '', $synopsis, ': ' . $description ] );
		}

		return '/**' . PHP_EOL . '* ' . implode( PHP_EOL . '* ', $longdesc ) . PHP_EOL . '*/';
	}

	public function name(): string {
		if ( ! is_string( $this->name ) || '' === $this->name ) {
			throw new RuntimeException( '@todo' );
		}

		return $this->name;
	}

	public function options(): array {
		return array_map( function ( $synopsis, $description ) {
			$parsed = SynopsisParser::parse( $synopsis )[0];
			$parsed['description'] = $description;

			return $parsed;
		}, array_keys( $this->options ), $this->options );
	}

	public function shortdesc(): string {
		if ( ! is_string( $this->description ) || '' === $this->description ) {
			throw new RuntimeException( '@todo' );
		}

		return $this->description;
	}

	public function synopsis(): string {
		$all_param_synopses = array_merge(
			array_keys( $this->arguments ),
			array_keys( $this->options )
		);

		return implode( ' ', $all_param_synopses );
	}
}
