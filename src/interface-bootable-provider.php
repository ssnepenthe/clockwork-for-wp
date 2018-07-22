<?php

namespace Clockwork_For_Wp;

interface Bootable_Provider {
	/**
	 * @param  Plugin $container
	 * @return void
	 */
	public function boot( Plugin $container );
}
