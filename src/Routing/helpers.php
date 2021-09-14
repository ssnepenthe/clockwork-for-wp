<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

function build_unencoded_query( array $data ): string {
	return \implode(
		'&',
		\array_map(
			static function ( $key, $value ) {
				return "{$key}={$value}";
			},
			\array_keys( $data ),
			$data
		)
	);
}
