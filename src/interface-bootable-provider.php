<?php

namespace Clockwork_For_Wp;

interface Bootable_Provider {
	public function boot( Plugin $container );
}
