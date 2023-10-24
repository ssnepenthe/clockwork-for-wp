<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork_For_Wp\Data_Source\Data_Source_Factory;

/**
 * @internal
 */
final class Clockwork_Support {
	private $clockwork;

	private $config;

	private $factory;

	public function __construct( Clockwork $clockwork, Data_Source_Factory $factory, Read_Only_Configuration $config ) {
		$this->clockwork = $clockwork;
		$this->factory = $factory;
		$this->config = $config;
	}

	public function add_data_sources(): void {
		$sensitive_patterns = $this->config->get( 'requests.sensitive_patterns' );

		$this->clockwork->addDataSource(
			$this->factory->create( 'php', \compact( 'sensitive_patterns' ) )
		);

		foreach ( $this->factory->get_enabled_data_sources() as $data_source ) {
			$this->clockwork->addDataSource( $data_source );
		}
	}

	public function extend_request( $data ) {
		$this->add_data_sources();

		return $this->clockwork->extendRequest( $data );
	}
}
