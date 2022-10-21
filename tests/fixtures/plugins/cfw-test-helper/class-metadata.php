<?php

namespace Cfw_Test_Helper;

use League\Config\ConfigurationInterface;

// @todo Having some issues with the FileStorage implementation where oldest file is disregarded.
//       Use a rough manual implementation for now and revisit after Clockwork v6 drops.
//       https://github.com/itsgoingd/clockwork/issues/510
class Metadata {
	public static function dir() {
		$config = \_cfw_instance()->getContainer()->get( ConfigurationInterface::class );

		if ( 'file' !== $config->get( 'storage.driver' ) ) {
			throw new \RuntimeException(
				'Clockwork storage driver must be set to "file" for tests'
			);
		}

		if ( '' === $config->get( 'storage.drivers.file.path' ) ) {
			throw new \RuntimeException( '@todo' );
		}

		return rtrim( $config->get( 'storage.drivers.file.path' ), '/\\' );
	}

	public static function list_all() {
		return \glob( static::dir() . '/*.json' );
	}

	public static function list_all_with_index() {
		$dir = static::dir();

		return \glob( "{{$dir}/*.json,{$dir}/index}", \GLOB_BRACE );
	}

	public static function cleanup() {
		$all_with_index = static::list_all_with_index();

		if ( is_array( $all_with_index ) ) {
			foreach ( $all_with_index as $file ) {
				\unlink( $file );
			}
		}
	}

	public static function find( $id ) {
		$file = static::dir() . "/{$id}.json";

		if ( ! \is_readable( $file ) ) {
			if ( ! \file_exists( $file ) ) {
				throw new \InvalidArgumentException( "Metadata with ID {$id} does not exist" );
			} else {
				throw new \RuntimeException( "Metadata with ID {$id} not readable" );
			}
		}

		$request = \json_decode( \file_get_contents( $file ), true );

		if ( null === $request || \JSON_ERROR_NONE !== \json_last_error() ) {
			throw new \RuntimeException( "Metadata with ID {$id} contains invalid JSON" );
		}

		return $request;
	}
}
