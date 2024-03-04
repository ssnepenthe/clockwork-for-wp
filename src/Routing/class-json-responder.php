<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use SimpleWpRouting\Responder\ComposableResponder;
use SimpleWpRouting\Responder\Partial\JsonPartial;

final class Json_Responder extends ComposableResponder {
	/**
	 * @param int<200, 299>|int<400, 599> $status_code
	 */
	public function __construct( $data, int $status_code = 200, int $options = 0 ) {
		$this->getPartialSet()
			->get( JsonPartial::class )
			->dontEnvelopeResponse()
			->setData( $data )
			->setStatusCode( $status_code )
			->setOptions( $options );
	}

	protected function createPartials(): array {
		return [
			new JsonPartial(),
		];
	}
}
