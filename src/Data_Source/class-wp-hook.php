<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Subscriber\Wp_Hook_Subscriber;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Provides_Subscriber;

use function Clockwork_For_Wp\describe_callable;
use function Clockwork_For_Wp\describe_unavailable_callable;

final class Wp_Hook extends DataSource implements Provides_Subscriber {
	private $all_hooks;

	private $hooks = [];

	public function __construct( bool $all_hooks = false ) {
		$this->all_hooks = $all_hooks;
	}

	public function add_hook(
		string $tag,
		?int $priority = null,
		$callback = null,
		?int $accepted_args = null
	): void {
		if ( null === $callback ) {
			$callback_description = '';
		} else {
			$callback_description = \is_callable( $callback )
				? describe_callable( $callback )
				: describe_unavailable_callable( $callback );
		}

		$hook = [
			'Tag' => $tag,
			'Priority' => null !== $priority ? (string) $priority : '',
			'Callback' => $callback_description,
			'Accepted Args' => null !== $accepted_args ? (string) $accepted_args : '',
		];

		if ( ! $this->passesFilters( [ $hook ] ) ) {
			return;
		}

		// @todo Should empty values be filtered out?
		$this->hooks[] = $hook;
	}

	public function create_subscriber(): Subscriber {
		return new Wp_Hook_Subscriber( $this );
	}

	public function get_all_hooks(): bool {
		return $this->all_hooks;
	}

	public function resolve( Request $request ) {
		if ( \count( $this->hooks ) > 0 ) {
			$request->userData( 'Hooks' )->table( 'Hooks', $this->hooks );
		}

		return $request;
	}
}
