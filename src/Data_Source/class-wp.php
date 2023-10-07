<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Subscriber\Wp_Subscriber;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Provides_Subscriber;

final class Wp extends DataSource implements Provides_Subscriber {
	private $variables = [];

	// @todo Records_Variables trait to share with Wp_Query data source?
	public function add_variable( $var, $value ) {
		$this->variables[] = [
			'Variable' => $var,
			'Value' => $value,
		];

		return $this;
	}

	public function create_subscriber(): Subscriber {
		return new Wp_Subscriber( $this );
	}

	public function resolve( Request $request ) {
		if ( 0 !== \count( $this->variables ) ) {
			$request->userData( 'WordPress' )->table( 'Request', $this->variables );
		}

		return $request;
	}
}
