<?php

namespace Cfw_Test_Helper;

function request_by_id() {
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

function clean_requests() {
	try {
		Metadata::cleanup();

		\wp_send_json_success();
	} catch ( \Exception $e ) {
		\wp_send_json_error();
	}
}

function create_requests() {
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

	update_option( CONFIG_KEY, $_REQUEST['config'] );

	wp_send_json_success();
}

function reset_config() {
	delete_option( CONFIG_KEY );

	wp_send_json_success();
}
