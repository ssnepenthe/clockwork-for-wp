<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Xdebug;
use WpEventDispatcher\SubscriberInterface;

/**
 * @internal
 */
final class Xdebug_Subscriber implements SubscriberInterface {
	private Xdebug $data_source;

	public function __construct( Xdebug $data_source ) {
		$this->data_source = $data_source;
	}

	public function getSubscribedEvents(): array {
		return [
			'init' => 'on_init',
		];
	}

	public function on_init(): void {
		$filename = \xdebug_get_profiler_filename();

		/**
		 * @psalm-suppress RedundantCondition
		 *
		 * @see https://github.com/vimeo/psalm/issues/6484
		 */
		if ( \is_string( $filename ) ) {
			$this->data_source->set_profiler_filename( $filename );
		}
	}
}
