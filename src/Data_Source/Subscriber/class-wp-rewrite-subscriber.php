<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Wp_Rewrite;
use Clockwork_For_Wp\Globals;
use WpEventDispatcher\SubscriberInterface;

/**
 * @internal
 */
final class Wp_Rewrite_Subscriber implements SubscriberInterface {
	private Wp_Rewrite $data_source;

	public function __construct( Wp_Rewrite $data_source ) {
		$this->data_source = $data_source;
	}

	public function getSubscribedEvents(): array {
		return [
			'cfw_pre_resolve' => 'on_cfw_pre_resolve',
		];
	}

	public function on_cfw_pre_resolve(): void {
		$wp_rewrite = Globals::get( 'wp_rewrite' );

		$this->data_source
			->set_structure( $wp_rewrite->permalink_structure )
			->set_trailing_slash( $wp_rewrite->use_trailing_slashes )
			->set_front( $wp_rewrite->front )
			->set_rules( $wp_rewrite->wp_rewrite_rules() );
	}
}
