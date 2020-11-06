<?php

namespace Clockwork_For_Wp\Tests\fixtures;

use RuntimeException;
use Symfony\Component\Finder\Finder;
use WP_CLI;

if ( ! defined( 'WP_CLI' ) ) {
	throw new RuntimeException( 'undefined wp cli' );
}

if ( ! WP_CLI ) {
	throw new RuntimeException( 'falsy wp cli' );
}

require_once __DIR__ . '/../bootstrap.php';

function finder() {
	return Finder::create()
		->files()
		->in( __DIR__ . '/../../../../cfw-data' );
}

// @todo Consider bundling in the actual plugin.
WP_CLI::add_command( 'cfw-clean', function() {
	// @todo Having an issue with FileStorage::cleanup( true ) where one file always remains.
	//       Use a manual implementation for now and revisit later when I have some more time.
	//       Or maybe not... I want finder available for other commands anyway.
	$finder = finder()->name( 'index' )->name( '*.json' );

	foreach ( $finder as $file ) {
		unlink( $file->getRealPath() );
	}

	WP_CLI::log( 'Clockwork metadata cleaned successfully' );
} );

WP_CLI::add_command( 'cfw-list', function() {
	$finder = finder()->name( '*.json' );

	$file_objects = iterator_to_array( $finder, false );
	$files = array_map( function( $file_object ) {
		return $file_object->getRealPath();
	}, $file_objects );

	WP_CLI::log( json_encode( $files ) );
} );
