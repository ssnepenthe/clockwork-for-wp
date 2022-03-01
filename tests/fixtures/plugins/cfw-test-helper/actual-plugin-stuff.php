<?php

namespace Cfw_Test_Helper;

function apply_config( $config ) {
	$request_config = get_option( CONFIG_KEY, null );

	if ( ! is_array( $request_config ) ) {
		$request_config = $_GET;
	}

	foreach ( ( new Config_Fetcher( $request_config ) )->get_config() as $key => $value ) {
		$config->set( $key, $value );
	}

	$requests_except = $config->get( 'requests.except' );
	$requests_except[] = 'action=cfwth_';

	$config->set( 'requests.except', $requests_except );
};

function print_test_context() {
	$context = [
		'ajaxUrl' => \admin_url( 'admin-ajax.php', 'relative' ),
		'clockworkVersion' => \Clockwork\Clockwork::VERSION,
	];

	printf(
		'<span data-cy="test-context">%s</span><span data-cy="request-id">%s</span>',
		json_encode( $context ),
		\esc_html( \_cfw_instance()->get_container()->get( \Clockwork\Request\Request::class )->id )
	);
};
