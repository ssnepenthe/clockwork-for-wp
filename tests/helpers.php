<?php

namespace Clockwork_For_Wp\Tests;

function activate_plugins( string ...$plugins ) : void {
	Cli::wp( 'plugin', 'activate', ...$plugins )->mustRun();
}

function deactivate_plugins( string ...$plugins ) : void {
	Cli::wp( 'plugin', 'deactivate', ...$plugins )->mustRun();
}

function get_metadata_files_list() : array {
	return json_decode( Cli::wp( 'cfw-list' )->mustRun()->getOutput(), true );
}

function clean_metadata_files() : void {
	Cli::wp( 'cfw-clean' )->mustRun();
}
