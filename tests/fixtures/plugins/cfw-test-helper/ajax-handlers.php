<?php

namespace Cfw_Test_Helper;

function metadata_by_id() {
	if ( ! \array_key_exists( 'id', $_REQUEST ) ) {
		\wp_send_json_error();
	}

	try {
		$request = Metadata::find( $_REQUEST['id'] );

		\wp_send_json_success( $request );
	} catch ( \Exception $e ) {
		\wp_send_json_error();
	}
}

function metadata_count() {
	try {
		$file_list = Metadata::list_all();

		\wp_send_json_success( \count( $file_list ) );
	} catch ( \Exception $e ) {
		\wp_send_json_error();
	}
}

function clean_metadata() {
	try {
		Metadata::cleanup();

		\wp_send_json_success();
	} catch ( \Exception $e ) {
		\wp_send_json_error();
	}
}

function request_factory() {
	if ( ! \array_key_exists( 'qty', $_REQUEST ) ) {
		\wp_send_json_error();
	}

	$qty = min( max( 1, (int) $_REQUEST['qty'] ), 10 );
	$requests = [];

	$clockwork = ( new \Clockwork\Clockwork() )
		->storage( _cfw_instance()[ \Clockwork\Storage\StorageInterface::class ] );

	for ( $i = 0; $i < $qty; $i++ ) {
		$request = new \Clockwork\Request\Request();

		$requests[] = [
			'id' => $request->id,
			'updateToken' => $request->updateToken,
		];

		$clockwork
			->request( $request )
			->resolveRequest()
			->storeRequest();
	}

	\wp_send_json_success( $requests );
}

function set_config() {
	if ( ! array_key_exists( 'config', $_REQUEST ) ) {
		wp_send_json_error();
	}

	update_option( 'cfw_coh_config', $_REQUEST['config'] );

	wp_send_json_success();
}

function reset_config() {
	delete_option( 'cfw_coh_config' );

	wp_send_json_success();
}
