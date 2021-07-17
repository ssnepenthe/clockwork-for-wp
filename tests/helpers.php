<?php

namespace Clockwork_For_Wp\Tests;

function fixture_path( $file ) {
	return __DIR__ . "/fixtures/{$file}";
}

function clean_metadata_files() {
	// @todo Having an issue with FileStorage::cleanup( true ) where one file always remains.
	//       Use a manual implementation for now and revisit later when I have some more time.
	foreach ( Metadata::all_with_index() as $file ) {
		unlink( $file );
	}

	// @todo Return value???
}

function activate_plugins( string ...$plugins ) : void {
	Cli::wp( 'plugin', 'activate', ...$plugins )->mustRun();
}

function deactivate_plugins( string ...$plugins ) : void {
	Cli::wp( 'plugin', 'deactivate', ...$plugins )->mustRun();
}
