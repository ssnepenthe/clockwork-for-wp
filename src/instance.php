<?php

declare(strict_types=1);

use Clockwork_For_Wp\Plugin;

function _cfw_instance(): Plugin {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new Plugin( null, [
			'file' => \dirname( __DIR__ ) . '/clockwork-for-wp.php',
		] );
	}

	return $instance;
}
