<?php

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork_For_Wp\Data_Source\Data_Source_Factory;

/**
 * @internal
 */
class Clockwork_Support {
	protected $clockwork;
	protected $factory;

	public function __construct( Clockwork $clockwork, Data_Source_Factory $factory ) {
		$this->clockwork = $clockwork;
		$this->factory = $factory;
	}

	public function extend_request( $data ) {
		$this->add_data_sources();

		return $this->clockwork->extendRequest( $data );
	}

	public function add_data_sources(): void {
		$this->clockwork->addDataSource( $this->factory->create( 'php' ) );

		foreach ( $this->factory->get_enabled_data_sources() as $data_source ) {
			$this->clockwork->addDataSource( $data_source );
		}
	}
}
