<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Subscriber\Transients_Subscriber;
use Clockwork_For_Wp\Provides_Subscriber;
use InvalidArgumentException;
use WpEventDispatcher\SubscriberInterface;

final class Transients extends DataSource implements Provides_Subscriber {
	private $deleted = [];

	private $setted = [];

	public function create_subscriber(): SubscriberInterface {
		return new Transients_Subscriber( $this );
	}

	public function deleted( $key, $is_site = false ) {
		$this->deleted[] = $this->prepare( 'deleted', $key, null, null, $is_site );

		return $this;
	}

	public function resolve( Request $request ) {
		if ( \count( $this->setted ) > 0 ) {
			$request->userData( 'Caching' )->table( 'Setted Transients', $this->setted );
		}

		if ( \count( $this->deleted ) > 0 ) {
			$request->userData( 'Caching' )->table( 'Deleted Transients', $this->deleted );
		}

		return $request;
	}

	public function setted( $key, $value = null, $expiration = null, $is_site = false ) {
		$this->setted[] = $this->prepare( 'setted', $key, $value, $expiration, $is_site );

		return $this;
	}

	// @todo External helper function?
	private function prepare( $type, $key, $value = null, $expiration = null, $is_site = false ) {
		if ( ! \in_array( $type, [ 'setted', 'deleted' ], true ) ) {
			throw new InvalidArgumentException(
				"Invalid type {$type} - must be one of 'setted', 'deleted'"
			);
		}

		$for_size = $value;

		if ( null !== $for_size && ! \is_string( $for_size ) ) {
			$for_size = \serialize( $for_size );
		}

		return \array_filter(
			[
				'Type' => $type,
				'Key' => $key,
				'Value' => $value,
				'Expiration' => $expiration,
				'Is Site' => $is_site ? 'Yes' : 'No',
				'Size' => \is_string( $for_size ) ? \mb_strlen( $for_size ) : null,
			],
			static function ( $value ) {
				return null !== $value;
			}
		);
	}
}
