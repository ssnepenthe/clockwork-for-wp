<?php

declare(strict_types=1);

use Clockwork_For_Wp\Plugin;

function _cfw_instance(): Plugin {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new Plugin(
			null,
			[
				'dir' => \dirname( __DIR__ ),
			]
		);
	}

	return $instance;
}
